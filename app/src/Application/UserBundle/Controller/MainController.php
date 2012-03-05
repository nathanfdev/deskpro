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

namespace Application\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\People\EmailValidator;

class MainController extends AbstractController
{
    public function indexAction()
    {
        return $this->render('UserBundle:Main:index.html.twig');
    }

	public function standardErrorAction($error_message = '', $error_title = '', $code = 200, array $vars = array())
	{
		$tpl_standard = 'UserBundle:Main:error-standard.html.twig';
		$tpl_specific = "UserBundle:Main:error-{$code}.html.twig";

		$tpl = $tpl_standard;
		if (App::getTemplating()->exists($tpl_specific)) {
			$tpl = $tpl_specific;
		}

		$vars = array_merge($vars, array(
			'error_message' => $error_message,
			'error_title'   => $error_title
		));

		$res = $this->render($tpl, $vars);

		$res->setStatusCode($code);

		return $res;
	}

	public function acceptTempUploadAction()
	{
		$security_token = $this->in->getString('security_token');

		if (!$this->session->getEntity()->checkSecurityToken('attach_temp', $security_token)) {
			return $this->createJsonResponse(array(array(
				'error_code' => 'invalid_security_token'
			)), 403);
		}

		$file = $this->request->files->get('attach');
		if (is_array($file)) {
			$file = array_pop($file);
		}
		$accept = $this->container->getAttachmentAccepter();

		$error = $accept->getError($file, 'user');
		if ($error) {
			$error['error'] = $this->container->getTranslator()->phrase('user.attach_error_' . $error['error_code'], $error);
			return $this->createJsonResponse(array($error));
		}

		$blob = $accept->accept($file, true);

		return $this->createJsonResponse(array(array(
			'blob_id'           => $blob->getId(),
			'blob_auth_id'      => $blob->getAuthId(),
			'download_url'      => $blob->getDownloadUrl(true),
			'filename'          => $blob->getFilename(),
			'filesize_readable' => $blob->getReadableFilesize()
		)));
	}

	public function validateEmailAction($id, $auth)
	{
		$validator = EmailValidator::createFromId($id, $auth);

		if (!$validator) {
			$this->renderStandardError('@user.profile.error_invalid_email_code', '', 404);
		}

		$valdating_email = $validator->getValidatingEmail();

		$email_exists = App::getEntityRepository('DeskPRO:PersonEmail')->getEmail($validator->getValidatingEmail()->getEmail());
		if ($email_exists && $email_exists->person->id != $valdating_email->person->id) {
			return $this->render('UserBundle:Main:validate-email-exists.html.twig', array(
				'email' => $email,
				'person' => $validator->getPerson(),
				'ticket_ids' => $validator->getTicketIds()
			));
		}

		try {
			$email = $validator->validate();
		} catch (\OutOfBoundsException $e) {
			if ($e->getCode() == 100) {
				return $this->renderStandardError('@user.profile.error_dupe_email');
			} else {
				throw $e;
			}
		}

		return $this->render('UserBundle:Main:validate-email-success.html.twig', array(
			'email' => $email,
			'person' => $validator->getPerson(),
			'ticket_ids' => $validator->getTicketIds()
		));
	}

	public function jstellLoginAction($jstell, $security_token, $usersource_id = 0)
	{
		if (!$this->session->getEntity()->checkSecurityToken('jstell', $security_token)) {
			return $this->createResponse('', 403);
		}

		if ($this->person->isGuest()) {
			$person_data = array(
				'person_id' => 0
			);
		} else {
			$person_data = array(
				'person_id' => $this->person->id,
				'person_name' => $this->person->name,
				'person_email' => $this->person->getPrimaryEmailAddress(),
			);

			if ($usersource_id && $this->person->usersource_assoc[$usersource_id]) {
				$person_data = array_merge(
					$this->person->usersource_assoc[$usersource_id]->getData(),
					$person_data
				);
			}
		}

		$person_data = json_encode($person_data);

		$html = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<script type="text/javascript" charset="utf-8">
	function load() {
		window.opener['$jstell']($person_data);
		window.close();
	}
	</script>
</head>
<body onload="load()">
</body>
</html>
HTML;

		return $this->createResponse($html);
	}
}
