<?php

namespace Application\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Application\DeskPRO\App;

class MainController extends AbstractController
{
    public function indexAction()
    {
        return $this->render('UserBundle:Main:index.html.twig');
    }

	public function standardErrorAction($error_message, $error_title = '', $code = 200)
	{
		$res = $this->render('UserBundle:Main:standard-error.html.twig', array(
			'error_message' => $error_message,
			'error_title'   => $error_title
		));

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

		$file = $this->request->files->get('attach');
		$desc = App::getApi('filestorage')->createRandomPath();

		$desc->write(file_get_contents($file->getRealPath()), array(
			'content_type' => $file->getMimeType(),
			'filename' => $file->getClientOriginalName(),
			'is_temp' => true
		));

		$blob_id = $desc->getPath();
		$blob = App::getOrm()->getRepository('DeskPRO:Blob')->find($blob_id);

		return $this->createJsonResponse(array(array(
			'blob_id' => $blob->getId(),
			'blob_auth_id' => $blob->getAuthId(),
			'download_url' => $blob->getDownloadUrl(true),
			'filename' => $blob->getFilename(),
			'filesize_readable' => $blob->getReadableFilesize()
		)));
	}
}
