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

use \Application\DeskPRO\Entity;
use \Application\DeskPRO\App;
use \Orb\Util\Strings;
use \Orb\Util\Arrays;
use \Orb\Util\Util;

class SettingsController extends AbstractController
{
	public function labelsAction($label_type)
	{
		$this->rememberLastPage();

		$all_labels = App::getOrm()->createQuery("
			SELECT label
			FROM DeskPRO:LabelDef label
			WHERE label.label_type = ?1
			ORDER BY label.label ASC
		")->execute(array(1=>$label_type));

		return $this->render('AdminBundle:Settings:labels.html.twig', array(
			'all_labels' => $all_labels,
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