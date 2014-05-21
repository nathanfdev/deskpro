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

namespace Application\ApiBundle\Controller;

use Application\ApiBundle\PermissionStrategy\AdminManagePermission;
use Application\DeskPRO\ResourceScanner\TemplateFiles;
use Application\DeskPRO\Templating\EmailTemplatesDesc;
use Application\DeskPRO\Templating\Templates\TemplateCustom;
use Application\DeskPRO\Templating\Templates\TemplateSet;
use Orb\Util\Strings;

class TemplatesController extends AbstractController implements ProtectedControllerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getPermissionStrategy()
	{
		return new AdminManagePermission();
	}


	####################################################################################################################
	# get-template-info
	####################################################################################################################

	public function getTemplateInfoAction()
	{
		$tplfiles = new TemplateFiles();
		$map = $tplfiles->getUserTemplates();

		$custom_templates = $this->container->getSystemService('style')->getCustomTemplateInfo();

		$list = $tplfiles->groupMap($map, $custom_templates);

		return $this->createApiResponse(array(
			'list'             => $list,
			'custom_templates' => $custom_templates,
		));
	}

	####################################################################################################################
	# get-email-template-info
	####################################################################################################################

	public function getEmailTemplateInfoAction()
	{
		$tpl_desc = new EmailTemplatesDesc();
		$list = $tpl_desc->getProcessedList($this->container->getTranslator());

		$custom_templates = $this->container->getSystemService('style')->getCustomTemplateInfo();
		$custom_templates = array_filter($custom_templates, function($x) { return preg_match('#^DeskPRO:emails_#', $x['name']); });

		if ($custom_templates) {
			foreach ($list as &$type_coll) {
				foreach ($type_coll['groups'] as &$group_coll) {
					foreach ($group_coll['templates'] as &$tpl){
						if (isset($custom_templates[$tpl['name']])) {
							$tpl['is_custom'] = true;
						} else {
							$tpl['is_custom'] = false;
						}
					}
				}
			}
			unset($type_coll, $group_coll, $tpl);
		}

		$list['custom'] = array();
		$list['custom']['title'] = 'Custom Emails';
		$list['custom']['typeId'] = 'custom';
		$list['custom']['groups'] = array();
		$list['custom']['groups']['custom'] = array(
			'groupId'   => 'custom',
			'title'     => 'Custom Emails',
			'templates' => array()
		);

		$custom_emails = $this->db->fetchAll("SELECT id, name FROM templates WHERE name LIKE 'DeskPRO:emails_custom:%'");
		foreach ($custom_emails as $tpl) {
			$name = Strings::extractRegexMatch('#^DeskPRO:emails_custom:(.*?).html.twig$#', $tpl['name'], 1) . '.html';
			$list['custom']['groups']['custom']['templates'][] = array(
				'typeId'    => 'custom',
				'groupId'   => 'custom',
				'is_custom' => true,
				'title'     => $name,
				'desc'      => '',
				'name'      => $tpl['name'],
				'showName'  => 'emails_custom/' . $name,
			);
		}

		return $this->createApiResponse(array(
			'list'             => $list,
			'custom_templates' => $custom_templates
		));
	}

	####################################################################################################################
	# get-template
	####################################################################################################################

	public function getTemplateAction($name)
	{
		$set = $this->getTemplateSet();

		try {
			$template = $set->getTemplate($name);
		} catch (\InvalidArgumentException $e) {
			throw $this->createNotFoundException();
		}

		$data = $set->exportTemplateToArray(
			$template,
			$this->container->getTranslator(),
			!$this->container->getLanguageData()->isMultiLang()
		);

		return $this->createApiResponse($data);
	}

	####################################################################################################################
	# set-template
	####################################################################################################################

	public function setTemplateAction($name)
	{
		$set = $this->getTemplateSet();

		try {
			$template = $set->getCustomTemplate($name);
		} catch (\InvalidArgumentException $e) {
			if (!$this->in->getBool('create_new')) {
				throw $this->createNotFoundException();
			}
		}

		if (!$template) {
			$template = $set->createCustomTemplate($name);
		}

		$template_code = $template->getTemplateCode();

		if ($template->getType() == 'email') {
			$subject = $this->in->getStringRaw('template.subject');
			$body    = $this->in->getStringRaw('template.body');

			$template_code->setSubject($subject);
			$template_code->setBody($body);
		} else {
			$code = $this->in->getStringRaw('template.code');
			$template_code->setCode($code);
		}

		$set->saveTemplate($template);

		return $this->createSuccessResponse(array(
			'name' => $template->getName(),
		));
	}


	####################################################################################################################
	# delete-template
	####################################################################################################################

	public function deleteTemplateAction($name)
	{
		$set = $this->getTemplateSet();

		try {
			$template = $set->getTemplate($name);
		} catch (\InvalidArgumentException $e) {
			throw $this->createNotFoundException();
		}

		if (!($template instanceof TemplateCustom)) {
			throw $this->createNotFoundException();
		}

		$set->deleteTemplate($template);

		return $this->createSuccessResponse(array(
			'old_name' => $name,
		));
	}


	####################################################################################################################

	/**
	 * @return TemplateSet
	 */
	private function getTemplateSet()
	{
		$set = new TemplateSet(
			$this->em,
			$this->container->get('twig'),
			$this->container->getSystemService('style')
		);
		return $set;
	}
}