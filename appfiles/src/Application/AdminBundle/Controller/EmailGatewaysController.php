<?php

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Orb\Util\Arrays;

use Application\AdminBundle\Form\EditEmailGateway as EditEmailGatewayForm;
use Application\AdminBundle\FormModel\EditEmailGateway as EditEmailGatewayModel;

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

		return $this->render('AdminBundle:EmailGateways:list.html.twig', array(
			'all_gateways' => $all_gateways
		));
	}

	############################################################################
	# edit
	############################################################################

	public function editAccountAction($id)
	{
		if ($id) {
			$gateway = $this->em->find('DeskPRO:EmailGateway', $id);
			if (!$id) {
				throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
			}
		} else {
			$gateway = new \Application\DeskPRO\Entity\EmailGateway();
		}

		$editgateway = new EditEmailGatewayModel($gateway);
		$form = $this->get('form.factory')->create(new EditEmailGatewayForm(), $editgateway);

		if ($this->request->isPost()) {
			$this->ensureRequestToken('edit_gateway');
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {

				$this->em->getConnection()->beginTransaction();
				try {
					$editgateway->save();
					$this->em->getConnection()->commit();
				} catch (\Exception $e) {
					$this->em->getConnection()->rollback();
					throw $e;
				}

				$this->session->setFlash('saved', $gateway->title);
				return $this->redirectRoute('admin_emailgateways');
			}
		}

		return $this->render('AdminBundle:EmailGateways:edit-account.html.twig', array(
			'gateway' => $gateway,
			'form' => $form->createView(),
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
}
