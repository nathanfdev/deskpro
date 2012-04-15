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
 * @subpackage AdminBundle
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
				'core.deskpro_name'            => $_POST['settings']['core.deskpro_name'],
				'core.deskpro_url'             => $_POST['settings']['core.deskpro_url'],
				'core.site_name'               => $_POST['settings']['core.site_name'],
				'core.site_url'                => $_POST['settings']['core.site_url'],
				'core.helpdesk_disabled'       => empty($_POST['settings']['core.helpdesk_disabled']) ? 0 : 1,
				'core.cookie_path'             => $_POST['settings']['core.cookie_path'],
				'core.cookie_domain'           => $_POST['settings']['core.cookie_domain'],
				'core.cookie_domain'           => $_POST['settings']['core.cookie_domain'],
				'core.cookie_domain'           => $_POST['settings']['core.cookie_domain'],
				'core.use_gravatar'            => empty($_POST['settings']['core.use_gravatar']) ? 0 : 1,
				'core.redirect_correct_url'    => empty($_POST['settings']['core.redirect_correct_url']) ? 0 : 1,

				'core.attach_agent_maxsize'    => (int)$_POST['settings']['core.attach_agent_maxsize'],
				'core.attach_agent_must_exts'  => $_POST['settings']['core.attach_agent_must_exts'],
				'core.attach_agent_not_exts'   => $_POST['settings']['core.attach_agent_not_exts'],

				'core.attach_user_maxsize'     => (int)$_POST['settings']['core.attach_user_maxsize'],
				'core.attach_user_must_exts'   => $_POST['settings']['core.attach_user_must_exts'],
				'core.attach_user_not_exts'    => $_POST['settings']['core.attach_user_not_exts'],
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
		$settings_files = new \Application\DeskPRO\ResourceScanner\AdvancedSettings();
		$show_settings = $settings_files->getAllSettings();

		if (App::getSession()->checkSecurityToken('revert_all', $this->in->getString('revert_all'))) {
			$this->db->beginTransaction();
			try {

				foreach ($show_settings as $k => $v) {
					$this->db->delete('settings', array('name' => $k));
				}

				$this->db->commit();
			} catch (\Exception $e) {
				$this->db->rollback();
				throw $e;
			}

			return $this->redirectRoute('admin_settings_adv');
		}

		foreach ($show_settings as $k => &$v) {
			$set = $this->container->getSetting($k);
			$v = array('default' => $v, 'set' => $set);
		}

		return $this->render('AdminBundle:Settings:advanced.html.twig', array(
			'show_settings' => $show_settings
		));
	}

	/**
	 * Set a specific setting
	 */
	public function advancedSetAction($name)
	{
		$value = $this->in->getValue('value');
		App::getEntityRepository('DeskPRO:Setting')->updateSetting($name, $value);

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
		if (!App::getSetting('core.rewrite_urls') && !App::getSetting('core.done_rewrite_urls_check')) {
			$this->db->replace('settings', array(
				'name' => 'core.done_rewrite_urls_check',
				'value' => time(),
			));

			$url = App::getRequest()->getUriForPath('/__checkurlrewrite');
			$url_noindex = str_replace('/index.php/', '/', $url);

			$client = new \Zend\Http\Client(null, array('timeout' => 5));
			$client->setMethod(\Zend\Http\Request::METHOD_GET);
			$client->setUri($url_noindex);
			$result = $client->send();
			if ($result->isSuccess() && strpos($result->getBody(), 'dp_check_url_ok') !== false) {
				$this->db->replace('settings', array(
					'name' => 'core.rewrite_urls',
					'value' => '1',
				));
				return $this->redirectRoute('admin_welcome');
			}
		}

		// If coming from the importer we already know all this info
		if (App::getSetting('core.deskpro3importer') && !App::getSetting('core.setup_initial')) {

			// Update URL though
			$url = App::getRequest()->getUriForPath('/');
			$url = str_replace('/index.php/', '/', $url);
			$this->db->replace('settings', array(
				'name' => 'core.deskpro_url',
				'value' => $url,
			));

			App::getEntityRepository('DeskPRO:Setting')->updateSetting('core.setup_initial', '1');

			return $this->redirectRoute('admin');
		}

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

		$default_transport = $this->em->createQuery("
			SELECT t
			FROM DeskPRO:EmailTransport t
			WHERE t.match_type = 'all'
		")->getOneOrNullResult();
		$outgoing_email_form = $this->forward('AdminBundle:EmailTransports:editAccount', array('id' => $default_transport ? $default_transport->getId() : '0'), array('_partial' => 'setup'))->getContent();

		$initial_pop = $this->em->createQuery("
			SELECT t
			FROM DeskPRO:EmailGateway t
			WHERE t.is_enabled = true
			ORDER BY t.id ASC
		")->setMaxResults(1)->getOneOrNullResult();
		$incoming_email_form = $this->forward('AdminBundle:EmailGateways:editAccount', array('id' => $initial_pop ? $initial_pop->getId() : '0'), array('_partial' => 'setup'))->getContent();

		return $this->render('AdminBundle:Settings:quick-setup.html.twig', array(
			'setup' => $setup,
			'form' => $form->createView(),
			'errors' => $errors,
			'outgoing_email_form' => $outgoing_email_form,
			'incoming_email_form' => $incoming_email_form,

			// Existing values
			'license_code' => $this->container->getSetting('core.license'),
			'last_cron_run' => $this->container->getSetting('core.last_cron_run'),
			'default_transport' => $default_transport,
			'initial_pop' => $initial_pop,
		));
	}

	public function setSilentSettingsAction()
	{
		$timezone = $this->in->getString('timezone');
		$url = $this->in->getString('url');

		if ($timezone && !$this->container->getSetting('core.default_timezone')) {
			$this->container->get('deskpro.core.settings')->setSetting('core.default_timezone', $timezone);
		}
		if ($url && !$this->container->getSetting('core.deskpro_url')) {
			$this->container->get('deskpro.core.settings')->setSetting('core.deskpro_url', $url);
		}

		if ($this->container->getSetting('core.app_secret') == 'APP_SERCRET') {
			$this->container->get('deskpro.core.settings')->setSetting('core.app_secret', Strings::random(50, Strings::CHARS_ALPHANUM_IU));
		}

		return $this->createJsonResponse(array('success' => true));
	}

	public function checkCronAction()
	{
		if ($this->container->getSetting('core.last_cron_run')) {
			return $this->createJsonResponse(array('cron_okay' => true));
		}

		return $this->createJsonResponse(array('cron_okay' => false));
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
		$setup_initial = $this->container->getSetting('core.setup_initial');

		if ($this->in->getBool('complete')) {
			if ($setup_initial < 30) {
				App::getEntityRepository('DeskPRO:Setting')->updateSetting('core.setup_initial', '31');
				return $this->redirectRoute('admin');
			}
		}

		$got = $this->container->getPhpBinaryPath();
		if (!$got) {
			$got = '/path/to/php';
		}

		$last_run = $this->container->getSetting('core.cron_last_run');
		$path = realpath(DP_ROOT.'/../');

		return $this->render('AdminBundle:Settings:cron.html.twig', array(
			'last_run' => $last_run,
			'path' => $path,
			'php_path' => $got,
			'found_php_path' => $got != '/path/to/php',
			'show_complete_form' => ($setup_initial < 30)
		));
	}
}
