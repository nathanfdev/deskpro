<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\Entity;
use Application\DeskPRO\App;
use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Util;

class SettingsController extends AbstractController
{
	############################################################################
	# list
	############################################################################

	public function settingsAction()
	{
		if ($this->in->getBool('process')) {
			$update_settings = array(
				'core.deskpro_name'        => $_POST['settings']['core.deskpro_name'],
				'core.deskpro_url'         => $_POST['settings']['core.deskpro_url'],
				'core.site_name'           => $_POST['settings']['core.site_name'],
				'core.site_url'            => $_POST['settings']['core.site_url'],
				'core.helpdesk_disabled'   => empty($_POST['settings']['core.helpdesk_disabled']) ? 0 : 1,
				'core.force_ssl'           => empty($_POST['settings']['core.force_ssl']) ? 0 : 1,
				'core.force_domain'        => empty($_POST['settings']['core.force_domain']) ? 0 : 1,
				'core.cookie_path'         => $_POST['settings']['core.cookie_path'],
				'core.cookie_domain'       => $_POST['settings']['core.cookie_domain'],
				'core.cookie_domain'       => $_POST['settings']['core.cookie_domain'],
				'core.cookie_domain'       => $_POST['settings']['core.cookie_domain'],
				'core.use_gravatar'        => empty($_POST['settings']['core.use_gravatar']) ? 0 : 1,

				'core.attach_agent_maxsize'   => (int)$_POST['settings']['core.attach_agent_maxsize'],
				'core.attach_agent_must_exts' => $_POST['settings']['core.attach_agent_must_exts'],
				'core.attach_agent_not_exts'  => $_POST['settings']['core.attach_agent_not_exts'],

				'core.attach_user_maxsize'   => (int)$_POST['settings']['core.attach_user_maxsize'],
				'core.attach_user_must_exts' => $_POST['settings']['core.attach_user_must_exts'],
				'core.attach_user_not_exts'  => $_POST['settings']['core.attach_user_not_exts'],
			);
			array_walk($update_settings, 'trim');

			foreach ($update_settings as $k => $v) {
				App::getEntityRepository('DeskPRO:Setting')->updateSetting($k, $v);
			}

			return $this->redirectRoute('admin_settings');
		}

		$max_filesize = \Orb\Util\Env::getEffectiveMaxUploadSize();

		return $this->render('AdminBundle:Settings:settings.html.twig', array(
			'max_uploadsize' => $max_filesize,
			'max_uploadsize_readable' => \Orb\Util\Numbers::filesizeDisplay($max_filesize)
		));
	}


	############################################################################
	# advanced
	############################################################################

	/**
	 * View a plain list of settings
	 */
	public function advancedAction()
	{
		$settings_files = new \Application\DeskPRO\ResourceScanner\SettingFiles();
		$all_settings = $settings_files->getAllSettings();

		return $this->render('AdminBundle:Settings:advanced.html.twig', array(
			'all_settings' => $all_settings
		));
	}

	/**
	 * Set a specific setting
	 */
	public function advancedSetAction($name)
	{
		$value = $this->in->getValue('value');
		$setting = App::getEntityRepository('DeskPRO:Setting')->updateSetting($name, $value);

		return $this->createJsonResponse(array('success' => true));
	}

	############################################################################
	# labels
	############################################################################

	public function labelsAction($label_type)
	{
		/** @var $ldm \Application\DeskPRO\Labels\LabelDefManager */
		$ldm = $this->container->getSystemService('label_def_manager');

		$def_counts = $ldm->countDefs();

		$type = null;
		if ($label_type != 'all') {
			$type = array($label_type);
		}

		$order_by = 'alpha';
		if ($this->in->getString('order_by') == 'count') {
			$order_by = 'count';
		}
		$labels = $ldm->getLabelsAndCounts($type, $order_by);

		return $this->render('AdminBundle:Settings:labels.html.twig', array(
			'label_type'  => $label_type,
			'order_by'    => $order_by,
			'def_counts'  => $def_counts,
			'labels'      => $labels
		));
	}

	public function labelsAjaxNewAction()
	{
		$label_str = strtolower($this->in->getString('label'));

		// Invalid
		if (!preg_match('#^[a-z0-9\- ]+$#', $label_str)) {
			return $this->createJsonResponse(array('errorMessage' => 'Please only enter letters, numbers and dashes'));
		}

		/** @var $ldm \Application\DeskPRO\Labels\LabelDefManager */
		$ldm = $this->container->getSystemService('label_def_manager');

		$types = $this->in->getCleanValueArray('types', 'str_simple', 'discard');
		$display_type = null;
		if ($this->in->getString('display_type') != 'all') {
			$display_type = $this->in->getString('display_type');
		}

		$ldm->createLabelDef($label_str, $types);

		$label_count = $ldm->countLabelUsages($label_str, $display_type);
		$def_counts  = $ldm->countDefs();

		$html = $this->renderView('AdminBundle:Settings:labels-row.html.twig', array('label' => $label_str, 'count' => $label_count));

		return $this->createJsonResponse(array(
			'row_html' => $html,
			'def_counts' => $def_counts
		));
	}

	public function labelsAjaxDeleteAction($label_type)
	{
		$label_str = strtolower($this->in->getString('label'));

		/** @var $ldm \Application\DeskPRO\Labels\LabelDefManager */
		$ldm = $this->container->getSystemService('label_def_manager');
		$ldm->deleteLabelDef($label_str);

		$def_counts  = $ldm->countDefs();

		return $this->createJsonResponse(array(
			'success' => 1,
			'def_counts' => $def_counts
		));
	}

	public function renameLabelAction($label_type)
	{
		$old_label_str = strtolower($this->in->getString('old_label'));
		$new_label_str = strtolower($this->in->getString('new_label'));

		$type = null;
		if ($label_type != 'all') {
			$type = $label_type;
		}

		/** @var $ldm \Application\DeskPRO\Labels\LabelDefManager */
		$ldm = $this->container->getSystemService('label_def_manager');
		$ldm->renameLabelDef($old_label_str, $new_label_str, $type);

		$label_count = $ldm->countLabelUsages($new_label_str, $type);
		$html = $this->renderView('AdminBundle:Settings:labels-row.html.twig', array('label' => $new_label_str, 'count' => $label_count));

		$def_counts  = $ldm->countDefs();

		return $this->createJsonResponse(array(
			'success' => 1,
			'row_html' => $html,
			'def_counts' => $def_counts
		));
	}

	############################################################################
	# save-setting
	############################################################################

	public function saveSingleSettingAction($setting_name, $security_token)
	{
		if (!$this->session->getEntity()->checkSecurityToken('set_setting', $security_token)) {
			return $this->renderStandardTokenError();
		}

		App::getEntityRepository('DeskPRO:Setting')->updateSetting($setting_name, $this->in->getRaw('value'));

		return $this->createJsonResponse(array('success' => true));
	}


	############################################################################
	# quick-setup
	############################################################################

	public function quickSetupAction()
	{
		$setup = new \Application\AdminBundle\FormModel\QuickSetup();
		$form = $this->get('form.factory')->create(new \Application\AdminBundle\Form\QuickSetupType(), $setup);

		$errors = false;
		if ($this->in->getBool('process')) {
			$this->ensureRequestToken();
			$form->bindRequest($this->get('request'));

			$errors = $setup->getErrors();
			if (!$errors) {
				$setup->save();
				return $this->redirectRoute('admin');
			}
		}

		return $this->render('AdminBundle:Settings:quick-setup.html.twig', array(
			'setup' => $setup,
			'form' => $form->createView(),
			'errors' => $errors
		));
	}

	public function checkInternetAccessAction()
	{
		$time = App::getSetting('core.last_network_check');
		$checked = App::getSetting('core.network_check');

		$is_connected = false;

		// If we've never done it, or the check is an hour old
		if ((!$time || time()-$time > 3600) || !$checked) {
			\DeskPRO\Kernel\License::getLicense();// loads DP_LIC_SERVER

			$client = new \Zend\Http\Client(null, array('timeout' => 15));
			$client->setMethod(\Zend\Http\Request::METHOD_GET);
			$client->setUri(DP_LIC_SERVER . '/ping.json');
			try {
				$result = $client->send();
				$is_connected = true;
			} catch (\Exception $e) {
				$is_connected = false;
			}

			App::getEntityRepository('DeskPRO:Setting')->updateSetting('core.last_network_check', time());
			if ($is_connected) {
				App::getEntityRepository('DeskPRO:Setting')->updateSetting('core.network_check', '1');
			} else {
				App::getEntityRepository('DeskPRO:Setting')->updateSetting('core.network_check', '0');
			}
		} else {
			$is_connected = true;
		}

		return $this->createJsonResponse(array(
			'is_connected' => $is_connected
		));
	}

	############################################################################
	# cron-info
	############################################################################

	public function cronAction()
	{
		// TODO when have proper cron page with help etc, put this step back
		App::getEntityRepository('DeskPRO:Setting')->updateSetting('core.setup_initial', '31');
		return $this->redirectRoute('admin');

		$setup_initial = $this->container->getSetting('core.setup_initial');

		if ($this->in->getBool('complete')) {
			if ($setup_initial < 30) {
				App::getEntityRepository('DeskPRO:Setting')->updateSetting('core.setup_initial', '31');
				return $this->redirectRoute('admin');
			}
		}

		$try = array('/usr/bin/php', '/usr/local/bin/php', 'C:\\php5\\bin\\php.exe');
		$got = null;
		foreach ($try as $p) {
			if (is_executable($p)) {
				$got = $p;
				break;
			}
		}
		if (!$got) {
			$got = '/path/to/php';
		}

		$last_run = $this->container->getSetting('core.cron_last_run');
		$path = realpath(DP_ROOT.'/../');

		return $this->render('AdminBundle:Settings:cron.html.twig', array(
			'last_run' => $last_run,
			'path' => $path,
			'php_path' => $got,
			'show_complete_form' => ($setup_initial < 30)
		));
	}
}
