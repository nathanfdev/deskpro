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
 * @subpackage UserBundle
 */

namespace Application\UserBundle\Controller;

use Application\DeskPRO\App;
use Application\UserBundle\Form\ProfileType;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\PersonEmailValidating;
use Application\DeskPRO\Entity\TmpData;

class ProfileController extends AbstractController implements RequireUserInterface
{
	############################################################################
	# index
	############################################################################

	/**
	 * Shows emails, link to edit password, form to edit name and timezone
	 */
	public function indexAction()
	{
		$form = $this->get('form.factory')->create(new ProfileType(), $this->person);

		if ($this->get('request')->getMethod() == 'POST') {
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				App::getOrm()->persist($this->person);
				App::getOrm()->flush();
			}
		}

		$validating_emails = App::getEntityRepository('DeskPRO:PersonEmailValidating')->getForPerson($this->person);

		return $this->render('UserBundle:Profile:index.html.twig', array(
			'form' => $form->createView(),
			'validating_emails' => $validating_emails,
		));
	}


	############################################################################
	# change-password
	############################################################################

	public function changePasswordAction()
	{
		$invalid_current_password = false;
		$invalid_repeat_password = false;

		if ($this->in->getBool('process')) {

			$password = $this->in->getString('password');
			$password2 = $this->in->getString('password2');

			if (!$this->person->checkPassword($this->in->getString('current_password'))) {
				$invalid_current_password = true;
			} else if ($password != $password2) {
				$invalid_repeat_password = true;
			} else {
				$this->person->setPassword($password);
				$person = $this->person;
				App::getOrm()->transactional(function ($em) use ($person) {
					$em->persist($person);
				});
				return $this->redirectRoute('user_profile');
			}
		}

		if ($invalid_current_password OR $invalid_repeat_password) {
			$this->session->setFlash('invalid_current_password', $invalid_current_password);
			$this->session->setFlash('invalid_repeat_password', $invalid_repeat_password);
			return $this->redirectRoute('user_profile');
		}

		$this->session->setFlash('password_set', true);
		return $this->redirectRoute('user_profile');
	}


	############################################################################
	# setDefaultEmail
	############################################################################

	/**
	 * Switches the primary email address on the account
	 */
	public function setDefaultEmailAction($email_id)
	{
		$email = $this->person->getEmailId($email_id);

		if (!$email) {
			return $this->renderStandardError('@user.error.invalid_email_explain', '@user.error.invalid_email', 404);
		}

		if (!$email['is_validated']) {
			return $this->renderStandardError('@user.error.validate_to_use', '@user.error.not_validated_email', 409);
		}

		$person = $this->person;
		$person->primary_email = $email;

		App::getOrm()->transactional(function ($em) use ($person) {
			$em->persist($person);
		});

		return $this->redirectRoute('user_profile');
	}


	############################################################################
	# removeEmail
	############################################################################

	/**
	 * Removes an email address from the account
	 */
	public function removeEmailAction($email_id)
	{
		$email = $this->person->getEmailId($email_id);

		if (!$email) {
			return $this->renderStandardError('@user.error.invalid_email_explain', '@user.error.invalid_email', 404);
		}

		#------------------------------
		# Can we remove this address?
		#------------------------------

		$validated_emails = $this->person->getValidatedEmails();

		$pass_count = false;
		if (!$email['is_validated']) {
			if (count($validated_emails) >= 1) $pass_count = true;
		} else {
			if (count($validated_emails) >= 2) $pass_count = true; // 2 because 1 wil be this email
		}

		if (!$pass_count) {
			return $this->renderStandardError('@user.error.last_email_explain', '@user.error.last_email', 409);
		}

		#------------------------------
		# Do the remove now
		#------------------------------

		App::getOrm()->beginTransaction();
		$this->person->removeEmailAddressId($email['id']);
		App::getOrm()->persist($this->person);
		App::getOrm()->flush();
		App::getOrm()->commit();

		$this->session->setFlash('removed_email', $email['email']);

		return $this->redirectRoute('user_profile');
	}

	public function removeEmailValidatingAction($email_id)
	{
		$validating_email = App::findEntity('DeskPRO:PersonEmailValidating', $email_id);

		if (!$validating_email || $validating_email->person['id'] != $this->person['id']) {
			return $this->renderStandardError('@user.error.invalid_email_explain', '@user.error.invalid_email', 404);
		}

		App::getOrm()->transactional(function ($em) use ($validating_email) {
			$em->remove($validating_email);
			$em->flush();
		});

		$this->session->setFlash('removed_email', $validating_email['email']);

		return $this->redirectRoute('user_profile');
	}


	############################################################################
	# newEmail
	############################################################################

	public function newEmailAction()
	{
		$email_address = $this->in->getString('new_email');

		// Already have this email on their account
		if ($this->person->findEmailAddress($email_address)) {
			return $this->redirectRoute('user_profile');
		}

		$email_exists = App::getEntityRepository('DeskPRO:PersonEmail')->getEmail($email_address);
		if ($email_exists) {
			$person = $this->person;

			$vars = array(
				'email_subject' => new \Application\DeskPRO\Translate\DelegatePhrase('user.emails.subj_newemail_exists'),
				'email_exists' => $email_exists,
				'person' => $person
			);

			App::getTranslator()->setTemporaryLanguage($person->getLanguage(), function($tr, $lang) use ($vars, $person, $email_exists) {
				$email_subject = $tr->phrase($vars['email_subject']);
				$email_body = App::get('templating')->render('DeskPRO:emails_user:new-email-exists.html.twig', $vars);

				$message = App::getMailer()->createMessage();
				$message->setTo($email_exists->getEmail(), $person->getDisplayName());
				$message->setSubject($email_subject);
				$message->setBody($email_body, 'text/html');
				$message->enableQueueHint();

				App::getMailer()->send($message);
			});
		}

		$validating_email = new PersonEmailValidating($email_address);
		$validating_email['email'] = $email_address;
		$validating_email->person = $this->person;

		App::getOrm()->transactional(function ($em) use ($validating_email) {
			$em->persist($validating_email);
			$em->flush();
		});

		$this->_doSendValidationEmail($validating_email);

		$this->session->setFlash('new_email_validating', $validating_email['email']);

		return $this->redirectRoute('user_profile');
	}

	############################################################################
	# sendValidateEmailLink
	############################################################################

	protected function _doSendValidationEmail($validating_email)
	{
		$person = $this->person;

		$vars = array(
			'email_subject' => new \Application\DeskPRO\Translate\DelegatePhrase('user.emails.subj_newemail_validate'),
			'validating_email' => $validating_email
		);

		App::getTranslator()->setTemporaryLanguage($person->getLanguage(), function($tr, $lang) use ($vars, $person, $validating_email) {
			$email_subject = $tr->phrase($vars['email_subject']);
			$email_body = App::get('templating')->render('DeskPRO:emails_user:new-email-validate.html.twig', $vars);

			$message = App::getMailer()->createMessage();
			$message->setTo($validating_email->getEmail(), $person->getDisplayName());
			$message->setSubject($email_subject);
			$message->setBody($email_body, 'text/html');
			$message->enableQueueHint();

			App::getMailer()->send($message);
		});
	}

	/**
	 * Re-send the validation link
	 */
	public function sendValidateEmailLinkAction($email_id)
	{
		$validating_email = App::findEntity('DeskPRO:PersonEmailValidating', $email_id);

		if (!$validating_email || $validating_email->person['id'] != $this->person['id']) {
			return $this->renderStandardError('@user.error.invalid_email_explain', '@user.error.invalid_email', 404);
		}

		$this->_doSendValidationEmail($validating_email);

		$this->session->setFlash('resent_validation_email', $validating_email['email']);

		return $this->redirectRoute('user_profile');
	}

	############################################################################
	# subscriptions
	############################################################################

	public function subscriptionsAction()
	{
		$unsorted_subscriptions = App::getEntityRepository('DeskPRO:ContentSubscription')->getSubscriptionsForPerson($this->person);

		$subscriptions = array(
			'article' => array(),
			'download' => array(),
			'feedback' => array(),
			'news' => array(),
		);

		foreach ($unsorted_subscriptions as $s) {
			if ($s->article) {
				$subscriptions['article'][] = $s;
			} elseif ($s->download) {
				$subscriptions['download'][] = $s;
			} elseif ($s->feedback) {
				$subscriptions['feedback'][] = $s;
			} elseif ($s->news) {
				$subscriptions['news'][] = $s;
			}
		}

		return $this->render('UserBundle:Profile:subscriptions.html.twig', array(
			'subscriptions' => $subscriptions
		));
	}

	public function addSubscriptionAction($type, $id)
	{
		$ent = 'DeskPRO:' . ucfirst($type);
		$ent_class = 'Application\\DeskPRO\\Entity\\' . ucfirst($type);
		if (!class_exists($ent_class)) {
			return $this->createNotFoundException();
		}

		$object = App::findEntity($ent, $id);
		if (!$object) {
			return $this->createNotFoundException();
		}

		$sub = App::getEntityRepository('DeskPRO:ContentSubscription')->getSubscription($object, $this->person);
		if (!$sub) {
			$sub = \Application\DeskPRO\Entity\ContentSubscription::create($object, $this->person);
			$this->em->persist($sub);
			$this->em->flush($sub);
		}

		if ($this->request->isXmlHttpRequest()) {
			return $this->createJsonResponse(array('success' => true));
		}

		return $this->redirect($object->getLink());
	}

	public function delSubscriptionAction($type, $id)
	{
		$ent = 'DeskPRO:' . ucfirst($type);
		$ent_class = 'Application\\DeskPRO\\Entity\\' . ucfirst($type);
		if (!class_exists($ent_class)) {
			return $this->createNotFoundException();
		}

		$object = App::findEntity($ent, $id);
		if (!$object) {
			return $this->createNotFoundException();
		}

		$sub = App::getEntityRepository('DeskPRO:ContentSubscription')->getSubscription($object, $this->person);
		if ($sub) {
			$this->em->remove($sub);
			$this->em->flush($sub);
		}

		if ($this->request->isXmlHttpRequest()) {
			return $this->createJsonResponse(array('success' => true));
		}

		if ($this->in->getBool('return_sub')) {
			return $this->redirectRoute('user_profile_subs');
		}

		return $this->redirect($object->getLink());
	}
}
