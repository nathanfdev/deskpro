<?php

namespace Application\AdminBundle\Controller;

use \Application\DeskPRO\App;

class PortalController extends AbstractController
{
    public function indexAction()
	{
		return $this->render('AdminBundle:Portal:index.html.twig');
	}

	public function uploadFaviconAction()
	{
		$file = $this->request->files->get('file');
		$desc = App::getApi('filestorage')->createRandomPath();

		$im = new \Imagick();
		$im->readImage($file->getRealPath());
		$im->scaleImage(16, 16, true);
		$im->setImageFormat('ico');

		$file_content = $im->getImageBlob();

		$desc->write($file_content, array(
			'content_type' => $file->getMimeType(),
			'filename' => $file->getClientOriginalName()
		));

		$blob_id = $desc->getPath();
		$blob = App::getOrm()->getRepository('DeskPRO:Blob')->find($blob_id);

		App::getEntityRepository('DeskPRO:Setting')->updateSetting('core.favicon_blob_id', $blob_id);

		return $this->redirectRoute('admin_portal');
	}
}
