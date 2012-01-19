<?php

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
		} else {
			$gateway = new \Application\DeskPRO\Entity\EmailGateway();
			$transport = new \Application\DeskPRO\Entity\EmailTransport();
		}

		$editgateway = new EditEmailGatewayModel($gateway);
		$form = $this->get('form.factory')->create(new EditEmailGatewayForm(), $editgateway);

		$edittrans = new EditEmailTransportModel($transport);
		$trans_form = $this->get('form.factory')->create(new EditEmailTransportForm(), $edittrans);

		if ($this->request->isPost()) {
			$this->ensureRequestToken('edit_gateway');
			$form->bindRequest($this->get('request'));
			$trans_form->bindRequest($this->get('request'));

			if ($form->isValid()) {

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

				$this->session->setFlash('saved', $gateway->title);
				return $this->redirectRoute('admin_emailgateways');
			}
		}

		return $this->render('AdminBundle:EmailGateways:edit-account.html.twig', array(
			'gateway' => $gateway,
			'transport' => $transport,
			'form' => $form->createView(),
			'trans_form' => $trans_form->createView(),
			'editgateway' => $editgateway,
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
