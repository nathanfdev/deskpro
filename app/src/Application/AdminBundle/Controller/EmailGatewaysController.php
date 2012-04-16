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

		return $this->render('AdminBundle:EmailGateways:list.html.twig', array(
			'all_gateways' => $all_gateways,
			'all_transports' => $all_transports,
		));
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
			try {
				$conn = $gateway->getFetcher();
				$conn->test();
			} catch (\Exception $e) {
				if ($this->request->isXmlHttpRequest()) {
					return $this->createJsonResponse(array('error' => true, 'error_code' => 'connect_error', 'error_message' => $e->getMessage()));
				} else {
					$errors = array('message' => $e->getMessage());
				}
			}

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
						$found = $a;
						break;
					}
				}

				if (!$found) {
					$address = new EmailGatewayAddress();
					$address->match_type    = 'exact';
					$address->match_pattern = $editgateway->address;

					$new_addresses[] = $address;
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

							$gateway->linked_transport->title = 'Google Apps: ' . $editgateway->address;
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

				App::getEntityRepository('DeskPRO:Setting')->updateSetting('core.task_completed_incoming_email', time());

				if ($this->request->isXmlHttpRequest()) {
					return $this->createJsonResponse(array('success' => true));
				}

				if ($is_new) {
					$this->redirectRoute('admin_tickettriggers_edit', array('trigger_id' => '0', 'from_gateway' => $gateway->id));
				}

				$this->session->setFlash('saved', $gateway->title);
				return $this->redirectRoute('admin_emailgateways');
			}
		}

		$tpl = 'AdminBundle:EmailGateways:edit-account.html.twig';
		if ($this->request->isPartialRequest()) {
			$tpl = 'AdminBundle:EmailGateways:edit-account-form.html.twig';
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

		return $this->createJsonResponse(array('success' => true, 'is_enabled' => $gateway->is_enabled));
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

		try {
			$conn = $gateway->getFetcher();
			$conn->test();
		} catch (\Exception $e) {
			return $this->createJsonResponse(array('error' => true, 'error_code' => $e->getCode(), 'error_message' => $e->getMessage()));
		}

		return $this->createJsonResponse(array('success' => true));
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
