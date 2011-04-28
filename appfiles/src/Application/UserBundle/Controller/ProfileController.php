<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\UserBundle\Controller;

use Application\DeskPRO\App;
use Application\UserBundle\Form\ProfileForm;
use Application\DeskPRO\Entity\PersonEmail;
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
		$form = new ProfileForm('profile');

		$form->bind($this->get('request'), $this->person);

		if ($form->isSubmitted()) {
			App::getOrm()->persist($this->person);
			App::getOrm()->flush();
		}

		return $this->render('UserBundle:Profile:index.html.twig', array(
			'form' => $form
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

		return $this->render('UserBundle:Profile:change-password.html.twig', array(
			'invalid_current_password' => $invalid_current_password,
			'invalid_repeat_password' => $invalid_repeat_password
		));
	}


	############################################################################
	# emails
	############################################################################

	/**
	 * Displays current emails, form to add a new one, and links to delete or make
	 * default.
	 */
	public function emailsAction()
	{
		return $this->render('UserBundle:Profile:emails.html.twig', array(
			
		));
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
			return $this->renderStandardError('@user_profile.error_invalid_email_explain', '@user_profile.error_invalid_email', 404);
		}

		if (!$email['is_validated']) {
			return $this->renderStandardError('@user_profile.error_not_validated_setdefault', '@user_profile.error_not_validated_email', 409);
		}

		$person = $this->person;
		$person->primary_email = $email;

		App::getOrm()->transactional(function ($em) use ($person) {
			$em->persist($person);
		});

		return $this->redirectRoute('user_profile_emails');
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
			return $this->renderStandardError('@user_profile.error_invalid_email_explain', '@user_profile.error_invalid_email', 404);
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
			return $this->renderStandardError('@user_profile.error_last_email_explain', '@user_profile.error_last_email', 409);
		}

		#------------------------------
		# Do the remove now
		#------------------------------

		App::getOrm()->beginTransaction();
		$this->person->removeEmailAddressId($email['id']);
		App::getOrm()->persist($this->person);
		App::getOrm()->flush();
		App::getOrm()->commit();

		return $this->redirectRoute('user_profile_emails');
	}

	
	############################################################################
	# newEmail
	############################################################################

	public function newEmailAction()
	{
		$email_address = $this->in->getString('new_email');

		$email_exists = App::getEntityRepository('DeskPRO:PersonEmail')->findByEmail($email_address);
		if ($email_exists) {
			return $this->renderStandardError('@user_profile.error_email_exists_explain', '@user_profile.error_email_exists', 409);
		}

		$email = new PersonEmail($email_address);
		$email['email'] = $email_address;
		$this->person->addEmailAddress($email);

		App::getOrm()->transactional(function ($em) use ($email) {
			$em->persist($email);
		});

		return $this->redirectRoute('user_profile_emails');
	}

	
	############################################################################
	# validateEmail
	############################################################################

	/**
	 * Validates an email address, or shows form to send a new validation email
	 */
	public function validateEmailAction($email_id, $code = false)
	{
		$email = $this->person->getEmailId($email_id);

		if (!$email) {
			return $this->renderStandardError('@user_profile.error_invalid_email_explain', '@user_profile.error_invalid_email', 404);
		}

		if ($email['is_validated']) {
			return $this->render('UserBundle:Profile:validate-email-done.html.twig', array(
				'email' => $email
			));
		}

		if (!$code) $code = $this->in->getString('code');
		if ($code) {
			$tmp_data = App::getEntityRepository('DeskPRO:TmpData')->getByCode($code, 'email_validation');
			if ($tmp_data AND $tmp_data->getData('email_id') == $email['id']) {
				$email['is_validated'] = true;
				App::getOrm()->transactional(function ($em) use ($email) {
					$em->persist($email);
					$em->remove($tmp_data);
				});

				return $this->render('UserBundle:Profile:validate-email-done.html.twig', array(
					'email' => $email
				));
			}
		}

		return $this->render('UserBundle:Profile:validate-email.html.twig', array(
			'email' => $email
		));
	}


	############################################################################
	# sendValidateEmailLink
	############################################################################

	/**
	 * Re-send the validation link
	 */
	public function sendValidateEmailLinkAction($email_id)
	{
		$email = $this->person->getEmailId($email_id);

		if (!$email) {
			return $this->renderStandardError('@user_profile.error_invalid_email_explain', '@user_profile.error_invalid_email', 404);
		}

		if ($email['is_validated']) {
			return $this->redirectRoute('user_profile_emails');
		}

		$tmp_data = new TmpData();
		$tmp_data->setData('email_id', $email_id);
		$tmp_data->setType('email_validation');
		App::getOrm()->persist($tmp_data);
		App::getOrm()->flush();

		$email_subject = 'Validate your email address';
		$email_body = App::get('templating')->render('DeskPRO:emails_user:validate-email.html.twig', array(
			'email' => $email,
			'code' => $tmp_data->getCode(),
		));

		$message = App::getMailer()->createMessage();
		$message->setTo($email['email'], $this->person->getDisplayName());
		$message->setSubject($email_subject);
		$message->setBody($email_body, 'text/html');
		$message->enableQueueHint();

		App::getMailer()->send($message);

		return $this->render('UserBundle:Profile:validate-email-sent.html.twig', array(
			'email' => $email
		));
	}
}
