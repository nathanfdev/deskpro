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

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\App;

class PortalController extends AbstractController
{
	############################################################################
	# Portal
	############################################################################

    public function indexAction()
	{
		$default_portal_style = $this->container->get('deskpro.core.settings')->getDefaultGroup('user_style');

		return $this->render('AdminBundle:Portal:index.html.twig', array(
			'default_portal_style' => $default_portal_style
		));
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
			'content_type' => $file->getClientMimeType(),
			'filename' => $file->getClientOriginalName()
		));

		$blob_id = $desc->getPath();
		$blob = $this->em->getRepository('DeskPRO:Blob')->find($blob_id);

		$this->em->getRepository('DeskPRO:Setting')->updateSetting('core.favicon_blob_id', $blob_id);

		return $this->redirectRoute('admin_portal');
	}

	public function getEditorAction($type)
	{
		switch ($type) {
			case 'logo':
				return $this->getLogoEditorAction();
				break;

			case 'portal-title':
				return $this->render('AdminBundle:Portal:portal-title-editor.html.twig');
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

				$this->get('deskpro.core.settings')->getGroup('user_style');

				$css_vars = $this->in->getCleanValueArray('vars', 'string', 'string');
				$set_settings = array();
				foreach ($css_vars as $name => $value) {
					$setting_name = 'user_style.' . $name;
					$set_settings[$setting_name] = $value;
					$this->em->getRepository('DeskPRO:Setting')->updateSetting($setting_name, $value);
				}

				$this->get('deskpro.core.settings')->setTemporarySettingValues($set_settings);

				$css = $this->container->get('templating')->render('UserBundle:Css:main.css.twig', array());

				$desc = $this->container->getFilestorage()->createRandomPath();
				$desc->write($css, array(
					'content_type' => 'text/css',
					'filename' => 'main.css'
				));
				$blob_id = $desc->getPath();

				$this->db->update('styles', array('css_blob_id' => $blob_id), array('id' => $this->container->getSystemService('style')->getId()));

				break;

			case 'header_title':
				$this->container->getEm()->getRepository('DeskPRO:Setting')->updateSetting('core.deskpro_logo_blob', null);
				$this->container->getEm()->getRepository('DeskPRO:Setting')->updateSetting('user.portal_header', $this->in->getString('title'));
				$this->container->getEm()->getRepository('DeskPRO:Setting')->updateSetting('user.portal_tagline', $this->in->getString('tagline'));
				break;

			case 'header_logo':
				$blob = $this->container->getEm()->getRepository('DeskPRO:Blob')->getByAuthId($this->in->getString('blob_authid'));
				if ($blob) {
					$this->container->getEm()->getRepository('DeskPRO:Setting')->updateSetting('core.deskpro_logo_blob', $blob->id);
				}
				break;

			case 'disable_logo_area':
				$this->container->getSettingsHandler()->setSetting('user.portal_simpleheader', null);
				break;

			case 'enable_logo_area':
				$this->container->getSettingsHandler()->setSetting('user.portal_simpleheader', 1);
				break;

			case 'portal_title':
				$this->container->getSettingsHandler()->setSetting('user.portal_title', $this->in->getString('title'));
				break;

			case 'toggle_tab':
				if ($this->in->getBool('on')) {
					$val = 1;
				} else {
					$val = 0;
				}

				$this->container->getSettingsHandler()->setSetting('user.portal_tab_' . $this->in->getStrSimple('tab'), $val);

				break;
		}

		return $this->createJsonResponse(array('success' => true));
	}

	public function togglePortalAction()
	{
		$enable = $this->in->getBoolInt('enable');
		$this->em->getRepository('DeskPRO:Setting')->updateSetting('user.portal_enabled', $enable);

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

	public function feedbackSettingsAction()
	{
		return $this->render('AdminBundle:Portal:feedback-settings.html.twig');
	}
}
