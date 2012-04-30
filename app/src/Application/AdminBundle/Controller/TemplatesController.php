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
	####################################################################################################################
	# user-list
	####################################################################################################################

	/**
	 * Lists all user-related templates
	 */
	public function userListAction()
	{
		$tplfiles = new TemplateFiles();
		$map = $tplfiles->getUserTemplates();

		$custom_templates = $this->container->getSystemService('style')->getCustomTemplateInfo();

		$list = $this->groupMap($map, $custom_templates);

		// Put the DeskPRO ones last
        if(isset($list['DeskPRO'])) {
            $tmp = $list['DeskPRO'];
            unset($list['DeskPRO']);
            $list['DeskPRO'] = $tmp;
        }

		return $this->render('AdminBundle:Templates:user-templates.html.twig', array(
			'list' => $list,
			'custom_templates' => $custom_templates,
			'open_template' => $this->in->getString('open')
		));
	}


	####################################################################################################################
	# other-list
	####################################################################################################################

	/**
	 * Lists agent and admin templates
	 */
	public function otherListAction()
	{
		$tplfiles = new TemplateFiles();
		$map = $tplfiles->getOtherTemplates();

		$custom_templates = $this->container->getSystemService('style')->getCustomTemplateInfo();

		$list = $this->groupMap($map, $custom_templates);

		// Put the DeskPRO ones last
		$tmp = $list['DeskPRO'];
		unset($list['DeskPRO']);
		$list['DeskPRO'] = $tmp;

		return $this->render('AdminBundle:Templates:other-templates.html.twig', array(
			'list' => $list,
			'custom_templates' => $custom_templates,
		));
	}


	####################################################################################################################
	# email-list
	####################################################################################################################

	/**
	 * Lists agent and admin templates
	 */
	public function emailListAction()
	{
		$tplfiles = new TemplateFiles();
		$map = $tplfiles->getEmailTemplates();

		$custom_templates = $this->container->getSystemService('style')->getCustomTemplateInfo();

		$list = $this->groupMap($map, $custom_templates);

		$real_list = array(
			'DeskPRO' => array(
				'Layout' => $list['DeskPRO']['emails_common'],
				'User Emails' => $list['DeskPRO']['emails_user'],
				'Agent Emails' => $list['DeskPRO']['emails_agent'],
			)
		);

		return $this->render('AdminBundle:Templates:email-templates.html.twig', array(
			'list' => $real_list,
			'custom_templates' => $custom_templates,
		));
	}


	####################################################################################################################
	# get-template-code
	####################################################################################################################

	/**
	 * Gets the raw template code for template. This will be either the the current modified template, or the
	 * pristine template from the filesystem. This is used to populate the textarea in the editor.
	 */
	public function getTemplateCodeAction()
	{
		$tplfiles = new TemplateFiles();
		$map = $tplfiles->getTemplateMap();
		$name = $this->in->getString('name');

		$code = $this->db->fetchColumn("SELECT template_code FROM templates WHERE name = ?", array($name));
		if (!$code && isset($map[$name])) {
			$code = file_get_contents($map[$name]['path']);
		}
		if (!$code) {
			$code = '';
		}

		return $this->createResponse($code);
	}


	####################################################################################################################
	# revert-template
	####################################################################################################################

	/**
	 * Reverts a template back to default by deleting the database copy.
	 */
	public function revertTemplateAction()
	{
		$name = $this->in->getString('name');
		$this->db->delete('templates', array('name' => $name));

		return $this->createJsonResponse(array('success' => true, 'name' => $name));
	}


	####################################################################################################################
	# save-template
	####################################################################################################################

	/**
	 * Saves a template to the database. This involves compiling the template as well.
	 */
	public function saveTemplateAction()
	{
		$name = $this->in->getString('name');
		$this->db->delete('templates', array('name' => $name));

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

	####################################################################################################################

	/**
	 * Groups a template map into bundles/dirs for use in a template
	 *
	 * @param array $map
	 * @param array $custom_templates
	 * @return array
	 */
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

			$v['name'] = $k;
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

	####################################################################################################################
	# create-template
	####################################################################################################################

	public function createTemplateAction()
	{
		$name = $this->in->getString('name');

		if (!preg_match('#^([A-Za-z0-9]+):([A-Za-z0-9_]+):([A-Za-z0-9_\-\.]+)$#', $name)) {
			return $this->createJsonResponse(array('error' => true, 'error_message' => 'Invalid template name'));
		}

		$name = $this->in->getString('name');
		$this->db->delete('templates', array('name' => $name));

		$copy_tpl = $this->in->getString('copy_tpl');
		$code = '';
		if ($copy_tpl) {
			$tplfiles = new TemplateFiles();
			$map = $tplfiles->getTemplateMap();

			$code = $this->db->fetchColumn("SELECT template_code FROM templates WHERE name = ?", array($copy_tpl));
			if (!$code && isset($map[$copy_tpl])) {
				$code = file_get_contents($map[$copy_tpl]['path']);
			}
		}
		if (!$code) {
			$code = '';
		}

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

	####################################################################################################################
	# mini-manager
	####################################################################################################################

	public function miniManagerAction($dirname, $prefix)
	{
		$tplfiles = new TemplateFiles();
		$map = $tplfiles->getEmailTemplates();

		$custom_templates = $this->container->getSystemService('style')->getCustomTemplateInfo();
		$custom_templates = array_filter($custom_templates, function($v) use ($dirname, $prefix) {
			if (strpos($v['name'], "DeskPRO:custom_$dirname:$prefix") === 0) {
				return true;
			}
			return false;
		});

		$list = $this->groupMap($map, $custom_templates);
		$list = $list['DeskPRO'][$dirname];
		$list = array_filter($list['templates'], function($v) use ($list, $dirname, $prefix) {
			if (strpos($v['name'], "DeskPRO:$dirname:$prefix") === 0) {
				return true;
			}
			return false;
		});

		return $this->render('AdminBundle:Templates:mini-manager.html.twig', array(
			'default_templates' => $list,
			'custom_templates' => $custom_templates,
			'tpl_dirname' => $dirname,
			'tpl_prefix' => $prefix,
		));
	}

	####################################################################################################################
	# preview-email-template
	####################################################################################################################

	public function previewEmailTemplateAction($tpl)
	{
		$vars = array();

		$ticket = $this->em->createQuery("SELECT t FROM DeskPRO:Ticket t WHERE t.status != 'hidden' ORDER BY t.id DESC")->setMaxResults(1)->getSingleResult();
		$vars['ticket']      = $ticket;
		$vars['person']      = $this->person;
		$vars['access_code'] = $ticket->getAccessCode();

		$messages = $this->em->getRepository('DeskPRO:TicketMessage')->getTicketMessages($ticket,array(
			'limit' => 25,
			'order' => 'DESC',
			'with_notes' => true
		));
		$vars['messages'] = $messages;

		$html = $this->container->getTemplating()->render($tpl, $vars);

		$subject = '';
		if (strpos($html, '___DP___SUBJECT___SEP___') !== false) {
			list ($subject, $html) = explode('___DP___SUBJECT___SEP___', $html, 2);
			$subject = trim($subject);
			$html = trim($html);
		}

		$body = '<html><body>' . ($subject ? 'Subject: ' . htmlspecialchars($subject) . '<hr />' : '') . $html . '</body></html>';

		return $this->createResponse($body);
	}
}