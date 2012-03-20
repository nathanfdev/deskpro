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
 * @subpackage
 */

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\ResourceScanner\TemplateFiles;
use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Template;

class TemplatesController extends AbstractController
{
	public function userListAction()
	{
		$tplfiles = new TemplateFiles();
		$map = $tplfiles->getUserTemplates();

		$custom_templates = $this->container->getSystemService('style')->getCustomTemplateInfo();

		$list = $this->groupMap($map, $custom_templates);

		// Put the DeskPRO ones last
		$tmp = $list['DeskPRO'];
		unset($list['DeskPRO']);
		$list['DeskPRO'] = $tmp;

		return $this->render('AdminBundle:Templates:user-templates.html.twig', array(
			'list' => $list,
			'custom_templates' => $custom_templates,
		));
	}

	public function getTemplateCodeAction()
	{
		$tplfiles = new TemplateFiles();
		$map = $tplfiles->getTemplateMap();
		$name = $this->in->getString('name');

		$code = App::getDb()->fetchColumn("SELECT template_code FROM templates WHERE name = ?", array($name));
		if (!$code && isset($map[$name])) {
			$code = file_get_contents($map[$name]['path']);
		}

		return $this->createResponse($code);
	}

	public function revertTemplateAction()
	{
		$name = $this->in->getString('name');
		App::getDb()->delete('templates', array('name' => $name));

		return $this->createJsonResponse(array('success' => true, 'name' => $name));
	}

	public function saveTemplateAction()
	{
		$name = $this->in->getString('name');
		App::getDb()->delete('templates', array('name' => $name));

		$code = $this->in->getRaw('code');

		try {
			/** @var $twig \Application\DeskPRO\Twig\Environment */
			$twig = $this->container->get('twig');
			$compiled = $twig->compileSource($code, $name);
		} catch (\Twig_Error_Syntax $e) {
			return $this->createJsonResponse(array(
				'error' => true,
				'error_syntax' => true,
				'error_code' => $e->getCode(),
				'error_message' => $e->getMessage(),
				'error_line' => $e->getTemplateLine()
			));
		} catch (\Twig_Error $e) {
			return $this->createJsonResponse(array(
				'error' => true,
				'error_code' => $e->getCode(),
				'error_message' => $e->getMessage()
			));
		}

		$template = new Template();
		$template->style = $this->container->getSystemService('style');
		$template->name = $name;
		$template->setTemplate($code, $compiled);

		$this->db->beginTransaction();
		try {
			$this->em->persist($template);
			$this->em->flush();
			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		return $this->createJsonResponse(array('success' => true, 'name' => $name));
	}

	protected function groupMap(array $map, array $custom_templates)
	{
		$grouped = array();

		foreach ($map as $k => $v) {
			preg_match('#^(.*?):(.*?):(.*?)$#', $k, $m);
			$bundle = $m[1];
			$dir = $m[2];
			if (!$dir) {
				$dir = '!top';
			}

			if (!isset($grouped[$bundle])) $grouped[$bundle] = array();
			if (!isset($grouped[$bundle][$dir])) $grouped[$bundle][$dir] = array('count_changed' => 0, 'count_outdated' => 0, 'templates' => array());

			$v['shortname'] = str_replace('.twig', '', $m[3]);

			if (isset($custom_templates[$k])) {
				$v['is_custom'] = true;
				$grouped[$bundle][$dir]['count_changed']++;

				$time = strtotime($custom_templates[$k]['date_updated']);
				if ($time < $v['last_updated']) {
					$grouped[$bundle][$dir]['count_outdated']++;
					$v['is_outdated'] = true;
				}
			}

			$grouped[$bundle][$dir]['templates'][$k] = $v;
		}

		ksort($grouped, \SORT_STRING);

		foreach ($grouped as &$bundle_dirs) {
			ksort($bundle_dirs, \SORT_STRING);
		}

		return $grouped;
	}
}