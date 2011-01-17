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
	public function labelsAction()
	{
		$all_labels = App::getOrm()->createQuery("
			SELECT label
			FROM DeskPRO:LabelDef label
			ORDER BY label.label ASC
		")->execute();

		return $this->render('AdminBundle:Settings:labels.twig.html', array(
			'all_labels' => $all_labels
		));
	}

	public function labelsAjaxNewAction()
	{
		$label_str = strtolower($this->in->getString('label'));
		$label_str = str_replace(' ', '-', $label_str);

		// Invalid
		if (!preg_match('#^[a-z0-9\-]+$#', $label_str)) {
			return $this->createJsonResponse(array('errorMessage' => 'Please only enter letters, numbers and dashes'));
		}

		// Already exists
		$label = App::getEntityRepository('DeskPRO:LabelDef')->find($label_str);
		if ($label) {
			return $this->createJsonResponse(array('errorMessage' => 'That label already exists'));
		}

		$label = new Entity\LabelDef();
		$label['label'] = $label_str;

		App::getOrm()->persist($label);
		App::getOrm()->flush();

		$html = $this->renderView('AdminBundle:Settings:labels-row.twig.html', array('label' => $label));

		return $this->createJsonResponse(array('html' => $html));
	}

	public function labelsAjaxDeleteAction()
	{
		$label = App::getEntityRepository('DeskPRO:LabelDef')->find($this->in->getString('label'));
		if (!$label) {
			return $this->createJsonResponse(array('errorMessage' => 'No such label exists'));
		}

		App::getOrm()->remove($label);
		App::getOrm()->flush();

		return $this->createJsonResponse(array('success' => 1));
	}
}