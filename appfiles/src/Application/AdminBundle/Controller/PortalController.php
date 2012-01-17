<?php

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\App;

class PortalController extends AbstractController
{
	############################################################################
	# Portal
	############################################################################

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

	public function saveEditorAction($type)
	{
		switch ($type) {
			case 'css_var':

				$css_vars = $this->in->getCleanValueArray('vars', 'string', 'string');
				$style = $this->container->getSystemService('style');

				foreach ($css_vars as $k => $v) {
					$style->setCssVar($k, $v);
				}

				$this->em->transactional(function($em) use ($style) {
					$em->persist($style);
					$em->flush();
				});

				break;
		}

		return $this->createJsonResponse(array('success' => true));
	}

	public function togglePortalAction()
	{
		$enable = $this->in->getBoolInt('enable');
		App::getEntityRepository('DeskPRO:Setting')->updateSetting('user.portal_enabled', $enable);

		return $this->redirectRoute('admin_portal');
	}

	public function updateBlockOrdersAction()
	{
		$helper = new \Application\AdminBundle\Controller\Helper\DisplayOrderUpdate($this);
		return $helper->doUpdate('portal_page_display');
	}

	public function blockToggleAction($pid)
	{
		$pd = $this->em->find('DeskPRO:PortalPageDisplay', $pid);
		if (!$pd) {
			return $this->createNotFoundException();
		}

		$pd->is_enabled = $this->in->getBool('enabled');

		$this->em->getConnection()->beginTransaction();

		try {
			$this->em->persist($pd);
			$this->em->flush();

			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		return $this->createJsonResponse(array('success'=>1));
	}

	############################################################################
	# Portal Sections
	############################################################################

	public function settingsAction()
	{
		return $this->render('AdminBundle:Portal:settings.html.twig');
	}

	public function ideaSettingsAction()
	{
		return $this->render('AdminBundle:Portal:idea-settings.html.twig');
	}
}
