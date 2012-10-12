<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
* DeskPRO
*
* @package DeskPRO
*/

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Orb\Util\Arrays;

use Application\AdminBundle\Form\EditEmailGateway as EditEmailGatewayForm;
use Application\AdminBundle\FormModel\EditEmailGateway as EditEmailGatewayModel;
use Application\AdminBundle\Form\EditEmailTransport as EditEmailTransportForm;
use Application\AdminBundle\FormModel\EditEmailTransport as EditEmailTransportModel;

use Application\DeskPRO\Entity\EmailGatewayAddress;

class EmailGatewaysController extends AbstractController
{
	############################################################################
	# list
	############################################################################

	/**
	 * Shows the main listing of gateways
	 */
	public function listAction()
	{
		$all_gateways = $this->em->createQuery("
			SELECT g
			FROM DeskPRO:EmailGateway g
			ORDER BY g.title ASC
		")->getResult();

		if (!count($all_gateways)) {
			return $this->redirectRoute('admin_emailgateways_new');
		}

		$all_transports = $this->em->getRepository('DeskPRO:EmailTransport')->findAll();

		$all_gateways_byemail = array();
		foreach ($all_gateways as $gateway) {
			foreach ($gateway->addresses as $addr) {
				$all_gateways_byemail[$addr->match_pattern] = $gateway;
			}
		}

		$helpdesk_emails = explode(',', $this->container->getSetting('core.helpdesk_emails'));

		return $this->render('@list.html.twig', array(
			'all_gateways' => $all_gateways,
			'all_transports' => $all_transports,
			'all_gateways_byemail' => $all_gateways_byemail,
			'helpdesk_emails' => $helpdesk_emails,
		));
	}

	public function saveHelpdeskAddressesAction()
	{
		$helpdesk_addresses = $this->in->getCleanValueArray('helpdesk_emails', 'string', 'discard');
		$helpdesk_addresses = implode(',', $helpdesk_addresses);

		$this->container->getSettingsHandler()->setSetting('core.helpdesk_emails', $helpdesk_addresses);

		return $this->redirectRoute('admin_emailgateways');
	}

	############################################################################
	# edit
	############################################################################

	public function editAccountAction($id)
	{
		if ($id) {
			$gateway = $this->em->find('DeskPRO:EmailGateway', $id);
			if (!$gateway) {
				throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
			}

			if ($gateway->linked_transport) {
				$transport = $gateway->linked_transport;
			} else {
				$transport = new \Application\DeskPRO\Entity\EmailTransport();
			}
			$is_new = false;
		} else {
			$is_new = true;
			$gateway = new \Application\DeskPRO\Entity\EmailGateway();
			$transport = new \Application\DeskPRO\Entity\EmailTransport();
		}

		$editgateway = new EditEmailGatewayModel($gateway);
		$form = $this->get('form.factory')->create(new EditEmailGatewayForm(), $editgateway);

		$edittrans = new EditEmailTransportModel($transport);
		$trans_form = $this->get('form.factory')->create(new EditEmailTransportForm(), $edittrans);
		$errors = array();

		if ($this->request->isPost()) {
			$this->ensureRequestToken('edit_gateway');
			$form->bindRequest($this->get('request'));
			$trans_form->bindRequest($this->get('request'));

			$editgateway->apply();

			if (!$errors && $form->isValid()) {

				$editgateway->define_transport = $this->in->getBool('gateway.define_transport');

				$new_addresses_info = $this->in->getCleanValueArray('new_address', 'array', 'str_simple');
				$new_addresses = array();

				foreach ($new_addresses_info as $address_info) {
					$address = new EmailGatewayAddress();
					$address->match_type    = 'exact';
					$address->match_pattern = $address_info['match_pattern'];

					$new_addresses[] = $address;
				}

				$found = false;

				foreach ($gateway->addresses as $a) {
					if ($a->match_pattern == $editgateway->address) {
						$found = true;
						break;
					}
				}

				if (!$found) {
					$address = new EmailGatewayAddress();
					$address->match_type    = 'exact';
					$address->match_pattern = $editgateway->address;

					$new_addresses[] = $address;

					if (count($gateway->addresses) == 1) {
						$this->em->remove($gateway->addresses->get(0));
						$gateway->addresses->remove(0);
					}
				}

				// Remove addresses
				$remove_address_ids = $this->in->getCleanValueArray('remove_address', 'uint', 'discard');

				$editgateway->setNewAddresses($new_addresses);
				$editgateway->setRemoveAddressIds($remove_address_ids);

				$edittrans->match_type = 'exact';
				$edittrans->match_email = $editgateway->address;

				$this->em->getConnection()->beginTransaction();
				try {
					$editgateway->save();
					$this->em->flush();

					if ($editgateway->define_transport) {
						$edittrans->save();
						$gateway->linked_transport = $transport;
					} else {
						if ($editgateway->connection_type == 'gmail') {
							if (!$gateway->linked_transport) {
								$gateway->linked_transport = new \Application\DeskPRO\Entity\EmailTransport();
							}

							$gateway->linked_transport->title = 'Gmail / Google Apps: ' . $editgateway->address;
							$gateway->linked_transport->match_type = 'exact';
							$gateway->linked_transport->match_pattern = $editgateway->address;
							$gateway->linked_transport->transport_type = 'gmail';
							$gateway->linked_transport->transport_options = $editgateway->gmail_options;

							$this->em->persist($gateway->linked_transport);

						} elseif ($gateway->linked_transport) {
							$this->em->remove($gateway->linked_transport);
							$gateway->linked_transport = null;
						}
					}

					$this->em->flush();
					$this->em->getConnection()->commit();
				} catch (\Exception $e) {
					$this->em->getConnection()->rollback();
					throw $e;
				}

				$this->em->getRepository('DeskPRO:Setting')->updateSetting('core.task_completed_incoming_email', time());

				if ($this->request->isXmlHttpRequest()) {
					return $this->createJsonResponse(array('success' => true));
				}

				$this->session->setFlash('saved', $gateway->title);
				return $this->redirectRoute('admin_emailgateways');
			}
		}

		$tpl = '@edit-account.html.twig';
		if ($this->request->isPartialRequest()) {
			$tpl = '@edit-account-form.html.twig';
		}

		return $this->render($tpl, array(
			'errors' => $errors,
			'gateway' => $gateway,
			'transport' => $transport,
			'form' => $form->createView(),
			'trans_form' => $trans_form->createView(),
			'editgateway' => $editgateway,
			'partial' => $this->request->isPartialRequest()
		));
	}

	public function quickToggleAction($id)
	{
		$gateway = $this->em->find('DeskPRO:EmailGateway', $id);
		if (!$id) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$gateway->is_enabled = !$gateway->is_enabled;

		$this->em->transactional(function ($em) use ($gateway) {
			$em->persist($gateway);
			$em->flush();
		});

		return $this->createJsonResponse(array('success' => true, 'is_enabled' => $gateway->is_enabled, 'gateway_id' => $gateway->getId()));
	}

	############################################################################
	# ajax-test
	############################################################################

	public function ajaxTestAction()
	{
		$gateway = new \Application\DeskPRO\Entity\EmailGateway();

		$editgateway = new EditEmailGatewayModel($gateway);
		$form = $this->get('form.factory')->create(new EditEmailGatewayForm(), $editgateway);
		$form->bindRequest($this->get('request'));
		$editgateway->apply();

		$logger = new \Orb\Log\Logger();
		$array_writer = new \Orb\Log\Writer\ArrayWriter();
		$logger->addWriter($array_writer);

		if ($gateway->connection_type == 'pop3') {
			if (empty($editgateway->pop3_options['host']) || empty($editgateway->pop3_options['username']) || empty($editgateway->pop3_options['password'])) {
				return $this->createJsonResponse(array(
					'error' => true,
					'error_explain' => 'Host, username and password must all be supplied',
					'error_code' => '0',
					'error_message' => 'Missing parameters',
					'log' => ''
				));
			}
		} elseif ($gateway->connection_type == 'gmail') {
			if (empty($editgateway->gmail_options['username']) || empty($editgateway->gmail_options['password'])) {
				return $this->createJsonResponse(array(
					'error' => true,
					'error_explain' => 'You must enter both a username and password',
					'error_code' => '0',
					'error_message' => 'Missing parameters',
					'log' => ''
				));
			}
		}

		try {
			$conn = $gateway->getFetcher();
			$conn->setLogger($logger);

			$count = $conn->test();
		} catch (\Exception $e) {

			$explain = 'There was an error while connecting to the server';

			if ($e->getCode() == \Application\DeskPRO\EmailGateway\Storage\Pop3::ERR_CONNECT) {
				if ($gateway->connection_type == 'pop3') {
					$explain = 'There was an error while trying to connect to the server. Make sure the host and port you specified is correct.';
				} elseif ($gateway->connection_type == 'gmail') {
					$explain = 'There was an error while trying to connect to the Google servers. This may be a temporary problem, you should try again.';
				}
			} elseif ($e->getCode() == \Application\DeskPRO\EmailGateway\Storage\Pop3::ERR_LOGIN) {
				if ($gateway->connection_type == 'pop3') {
					$explain = 'Your username and password appear to be invalid.';
				} elseif ($gateway->connection_type == 'gmail') {
					$explain = 'Your username and password appear to be invalid.';
				}
			}

			if ($e->getPrevious()) {
				$e = $e->getPrevious();
			}

			return $this->createJsonResponse(array(
				'error' => true,
				'error_explain' => $explain,
				'error_code' => \Orb\Util\Util::getBaseClassname($e) . '::' . $e->getCode(),
				'error_message' => $e->getMessage(),
				'log' => implode("\n", $array_writer->getMessages())
			));
		}

		return $this->createJsonResponse(array(
			'success' => true,
			'count' => $count,
		));
	}

	############################################################################
	# delete
	############################################################################

	public function deleteAction($id, $security_token)
	{
		$gateway = $this->em->find('DeskPRO:EmailGateway', $id);
		if (!$gateway || !$this->session->checkSecurityToken('delete_gateway', $security_token)) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$this->em->getConnection()->beginTransaction();

		try {
			$this->em->remove($gateway);
			$this->em->flush();

			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		$this->session->setFlash('deleted', $gateway->title);

		return $this->redirectRoute('admin_emailgateways');
	}
}
