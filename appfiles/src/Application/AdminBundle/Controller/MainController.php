<?php

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\App;

class MainController extends AbstractController
{
    public function indexAction()
	{
		// If we just came from the agent interface, lets redirect the
		// person back where they just were
		$ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : false;
		$old_url = App::getSession()->get('admin_last_page');
		if ($old_url AND $ref AND strpos($ref, '/agent/') !== false AND strpos($ref, '/admin/') === false) {
			App::getSession()->remove('admin_last_page');

			return $this->redirect($old_url);
		}

		return $this->render('AdminBundle:Main:index.html.twig');
	}

	public function acceptTempUploadAction()
	{
		$file = $this->request->files->get('file-upload');
		$desc = App::getApi('filestorage')->createRandomPath();

		$desc->write(file_get_contents($file->getRealPath()), array(
			'content_type' => $file->getMimeType(),
			'filename' => $file->getClientOriginalName()
		));

		$blob_id = $desc->getPath();
		$blob = App::getOrm()->getRepository('DeskPRO:Blob')->find($blob_id);

		if ($this->in->getString('attach_to_object')) {
			switch ($this->in->getString('attach_to_object')) {
				case 'article':
					$article = App::findEntity('DeskPRO:Article', $this->in->getUint('object_id'));

					$attach = new \Application\DeskPRO\Entity\ArticleAttachment();
					$attach['blob'] = $blob;
					$attach['person'] = $this->person;

					$article->addAttachment($attach);

					App::getOrm()->persist($article);
					App::getOrm()->flush();

					break;
			}
		}

		return $this->createJsonResponse(array(array(
			'blob_id' => $blob['id'],
			'blob_auth' => $blob->authcode,
			'blob_auth_id' => $blob->id . '-' . $blob->authcode,
			'download_url' => $blob->getDownloadUrl(true),
			'filename' => $blob['filename'],
			'filesize_readable' => $blob->getReadableFilesize()
		)));
	}
}
