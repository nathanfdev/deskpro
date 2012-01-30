<?php

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
			return $this->createJsonResponse(array(
				'error' => 'invalid_security_token'
			), 403);
		}

		try {
			$file = $this->request->files->get('attach');
			$desc = App::getApi('filestorage')->createRandomPath();

			$desc->write(file_get_contents($file->getRealPath()), array(
				'content_type' => $file->getClientMimeType(),
				'filename' => $file->getClientOriginalName(),
				'is_temp' => true
			));

			$blob_id = $desc->getPath();
			$blob = App::getOrm()->getRepository('DeskPRO:Blob')->find($blob_id);
		} catch (\Exception $e) {
			return $this->createJsonResponse(array(
				'error' => 'general',
			));
		}

		return $this->createJsonResponse(array(array(
			'blob_id' => $blob->getId(),
			'blob_auth_id' => $blob->getAuthId(),
			'download_url' => $blob->getDownloadUrl(true),
			'filename' => $blob->getFilename(),
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
