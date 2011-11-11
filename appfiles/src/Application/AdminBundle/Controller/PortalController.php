<?php

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\App;

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

	public function getEditorAction($type)
	{
		switch ($type) {
			case 'logo':
				return $this->getLogoEditorAction();
				break;
		}

		throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
	}

	public function getLogoEditorAction()
	{
		return $this->render('AdminBundle:Portal:portal-editor-logo.html.twig');
	}

	public function settingsAction()
	{
		return $this->render('AdminBundle:Portal:settings.html.twig');
	}

	public function ideaSettingsAction()
	{
		return $this->render('AdminBundle:Portal:idea-settings.html.twig');
	}

	public function ideaStatusesAction()
	{
		$active_status_cats  = App::getEntityRepository('DeskPRO:IdeaStatusCategory')->getActiveCategories();
		$closed_status_cats  = App::getEntityRepository('DeskPRO:IdeaStatusCategory')->getClosedCategories();
		return $this->render('AdminBundle:Portal:idea-statuses.html.twig', array(
			'active_status_cats' => $active_status_cats,
			'closed_status_cats' => $closed_status_cats
		));
	}
}
