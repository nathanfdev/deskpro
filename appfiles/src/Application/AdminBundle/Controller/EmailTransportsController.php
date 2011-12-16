<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Application\AdminBundle\Form\EditEmailTransport as EditEmailTransportForm;
use Application\AdminBundle\FormModel\EditEmailTransport as EditEmailTransportModel;

class EmailTransportsController extends AbstractController
{
	############################################################################
	# list
	############################################################################

	public function listAction()
	{
		$all_transports = $this->em->getRepository('DeskPRO:EmailTransport')->findAll();

		if (!count($all_transports)) {
			return $this->redirectRoute('admin_emailtrans_setup');
		}

		return $this->render('AdminBundle:EmailTransports:list.html.twig', array(
			'transports' => $all_transports,
		));
	}

	############################################################################
	# setup
	############################################################################

	public function setupAction()
	{
		$transport = new \Application\DeskPRO\Entity\EmailTransport();
		$transport->match_type = 'any';
		$transport->match_pattern = 'any';

		$edittrans = new EditEmailTransportModel($transport);
		$edittrans->match_type = 'any';
		$edittrans->match_pattern = 'any';
		$form = $this->get('form.factory')->create(new EditEmailTransportForm(), $edittrans);

		if ($this->request->isPost()) {
			$this->ensureRequestToken('edit_transport');
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {

				$this->em->getConnection()->beginTransaction();
				try {
					App::getEntityRepository('DeskPRO:Setting')->updateSetting('core.default_from_email', $this->in->getString('default_from_email'));
					$edittrans->save();
					$this->em->getConnection()->commit();
				} catch (\Exception $e) {
					$this->em->getConnection()->rollback();
					throw $e;
				}

				$this->session->setFlash('saved', $transport->title);
				return $this->redirectRoute('admin_emailtrans_list');
			} else {
				die('ere');
			}
		}

		return $this->render('AdminBundle:EmailTransports:setup.html.twig', array(
			'transport' => $transport,
			'form' => $form->createView(),
			'edittrans' => $edittrans,
		));
	}

	############################################################################
	# edit
	############################################################################

	public function editAccountAction($id)
	{
		if ($id) {
			$transport = $this->em->find('DeskPRO:EmailTransport', $id);
			if (!$id) {
				throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
			}
		} else {
			$transport = new \Application\DeskPRO\Entity\EmailTransport();
		}

		$edittrans = new EditEmailTransportModel($transport);
		$form = $this->get('form.factory')->create(new EditEmailTransportForm(), $edittrans);

		if ($this->request->isPost()) {
			$this->ensureRequestToken('edit_transport');
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {

				$this->em->getConnection()->beginTransaction();
				try {
					$edittrans->save();
					$this->em->getConnection()->commit();
				} catch (\Exception $e) {
					$this->em->getConnection()->rollback();
					throw $e;
				}

				$this->session->setFlash('saved', $transport->title);
				return $this->redirectRoute('admin_emailtrans_list');
			}
		}

		return $this->render('AdminBundle:EmailTransports:edit-account.html.twig', array(
			'transport' => $transport,
			'form' => $form->createView(),
			'edittrans' => $edittrans,
		));
	}

	############################################################################
	# setup
	############################################################################

	public function ajaxTestAction()
	{
		$transport = new \Application\DeskPRO\Entity\EmailTransport();

		$edittrans = new EditEmailTransportModel($transport);
		$form = $this->get('form.factory')->create(new EditEmailTransportForm(), $edittrans);
		$form->bindRequest($this->get('request'));
		$edittrans->apply();

		try {
			if ($this->in->getBool('backup')) {
				$tr = $transport->getBackupTransport();
			} else {
				$tr = $transport->getTransport();
			}

			$message = App::getMailer()->createMessage();
			$message->setTo($this->in->getString('send_to'));
			$message->setFrom($this->in->getString('send_from'));
			$message->setSubject('Test DeskPRO Email');
			$message->setBody('This is a test of the DeskPRO email system.');
			$message->setForceTransport($tr);

			$failed = array();
			App::getMailer()->send($message, $failed);

			if ($failed) {
				return $this->createJsonResponse(array('error' => true, 'error_code' => 'dp_1', 'error_message' => 'Connection succeeded, but the server was unable or unwilling to deliver the test email to ' . $this->in->getString('send_to')));
			}
		} catch (\Exception $e) {
			return $this->createJsonResponse(array('error' => true, 'error_code' => $e->getCode(), 'error_message' => $e->getMessage()));
		}

		return $this->createJsonResponse(array('success' => true));
	}
}
