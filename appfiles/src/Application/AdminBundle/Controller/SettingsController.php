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
				'core.deskpro_name'     => $_POST['settings']['core.deskpro_name'],
				'core.deskpro_url'      => $_POST['settings']['core.deskpro_url'],
				'core.site_name'        => $_POST['settings']['core.site_name'],
				'core.site_url'         => $_POST['settings']['core.site_url'],
				'core.helpdesk_enabled' => empty($_POST['settings']['core.helpdesk_enabled']) ? 0 : 1,
				'core.force_ssl'        => empty($_POST['settings']['core.force_ssl']) ? 0 : 1,
				'core.force_domain'     => empty($_POST['settings']['core.force_domain']) ? 0 : 1,
				'core.cookie_path'      => $_POST['settings']['core.cookie_path'],
				'core.cookie_domain'    => $_POST['settings']['core.cookie_domain'],
			);
			array_walk($update_settings, 'trim');

			foreach ($update_settings as $k => $v) {
				App::getEntityRepository('DeskPRO:Setting')->updateSetting($k, $v);
			}

			return $this->redirectRoute('admin_settings');
		}

		return $this->render('AdminBundle:Settings:settings.html.twig', array(

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
		$this->rememberLastPage();

		$all_labels = App::getOrm()->createQuery("
			SELECT label
			FROM DeskPRO:LabelDef label
			WHERE label.label_type = ?1
			ORDER BY label.label ASC
		")->execute(array(1=>$label_type));

		// Get a count for each
		$label_counts = App::getEntityRepository('DeskPRO:LabelDef')->getLabelCounts($label_type, false);

		return $this->render('AdminBundle:Settings:labels.html.twig', array(
			'all_labels' => $all_labels,
			'label_counts' => $label_counts,
			'label_type' => $label_type
		));
	}

	public function labelsAjaxNewAction($label_type)
	{
		$label_str = strtolower($this->in->getString('label'));

		// Invalid
		if (!preg_match('#^[a-z0-9\- ]+$#', $label_str)) {
			return $this->createJsonResponse(array('errorMessage' => 'Please only enter letters, numbers and dashes'));
		}

		// Already exists
		$label = App::getEntityRepository('DeskPRO:LabelDef')->find(array('label_type' => $label_type, 'label' => $label_str));
		if ($label) {
			return $this->createJsonResponse(array('errorMessage' => 'That label already exists'));
		}

		$label = new Entity\LabelDef();
		$label['label_type'] = $label_type;
		$label['label'] = $label_str;

		App::getOrm()->persist($label);
		App::getOrm()->flush();

		$html = $this->renderView('AdminBundle:Settings:labels-row.html.twig', array('label' => $label));

		return $this->createJsonResponse(array('html' => $html));
	}

	public function labelsAjaxDeleteAction($label_type)
	{
		$label_str = strtolower($this->in->getString('label'));
		$label = App::getEntityRepository('DeskPRO:LabelDef')->find(array('label_type' => $label_type, 'label' => $label_str));
		if (!$label) {
			return $this->createJsonResponse(array('errorMessage' => 'No such label exists'));
		}

		App::getOrm()->remove($label);
		App::getOrm()->flush();

		return $this->createJsonResponse(array('success' => 1));
	}

	public function renameLabelAction($label_type)
	{
		$old_label_str = strtolower($this->in->getString('old_label'));
		$new_label_str = strtolower($this->in->getString('new_label'));

		$old_label = App::getEntityRepository('DeskPRO:LabelDef')->find(array('label_type' => $label_type, 'label' => $old_label_str));

		App::getOrm()->beginTransaction();

		$label = App::getEntityRepository('DeskPRO:LabelDef')->find(array('label_type' => $label_type, 'label' => $new_label_str));
		if (!$label) {
			$label = new Entity\LabelDef();
			$label['label_type'] = $label_type;
			$label['label'] = $new_label_str;

			App::getOrm()->persist($label);
			App::getOrm()->flush();
		}

		$t = $label->getLabelTable();
		App::getDb()->executeUpdate("UPDATE IGNORE $t SET label = ? WHERE label = ?", array($old_label_str, $new_label_str));
		App::getDb()->executeUpdate("DELETE FROM $t WHERE label = ?", array($old_label_str));

		App::getOrm()->remove($old_label);
		App::getOrm()->flush();

		App::getOrm()->commit();

		// Redirect back to type
		return $this->redirectRoute('admin_labels', array('label_type' => $label_type));
	}
}
