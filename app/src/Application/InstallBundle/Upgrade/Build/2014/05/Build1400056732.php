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

namespace Application\InstallBundle\Upgrade\Build;

use Application\DeskPRO\Templating\Templates\TemplateSet;
use Application\InstallBundle\Data\DefaultDataProcessor;

class Build1400056732 extends AbstractBuild
{
	public function run()
	{
		$this->out("Re-compiling custom templates");

		$set = new TemplateSet(
			$this->container->getEm(),
			$this->container->get('twig'),
			$this->container->getSystemService('style')
		);

		$db = $this->container->getDb();

		$tids = $db->fetchAllCol("SELECT id FROM templates");
		$failed = array();
		$failed_data = array();

		$replacements = array(
			'DeskPRO:emails_user:new-reply-agent.html.twig' => 'DeskPRO:emails_user:ticket-reply-byagent.html.twig',
			'DeskPRO:emails_user:new-reply-user.html.twig'  => 'DeskPRO:emails_user:ticket-reply-autoreply.html.twig',
			'DeskPRO:emails_user:new-ticket.html.twig'      => 'DeskPRO:emails_user:ticket-new-autoreply.html.twig',
		);

		foreach ($tids as $id) {
			$info = $db->fetchAssoc("SELECT name, template_code FROM templates WHERE id = ?", array($id));
			$this->out("Re-compiling {$info['name']}");

			try {
				$code = $info['template_code'];
				$code = str_replace(array_keys($replacements), array_values($replacements), $code);

				$template      = $set->getCustomTemplate($info['name']);
				$template_code = $template->getTemplateCode();
				$template_code->setCode($code);
				$set->saveTemplate($template);
			} catch (\Exception $e) {
				$this->out("... Failed: {$e->getMessage()}");
				$failed[] = $id;
				$failed_data[] = $info;
			}
		}

		if ($failed) {
			$this->saveUpgradeData('201404', 'bad-templates', $failed_data);
			$db->executeUpdate("DELETE FROM templates WHERE id IN (" . implode(',', $failed) . ")");
		}

		// Rename custom templates
		$custom_names = $db->fetchAllKeyValue("SELECT id, name FROM templates WHERE name LIKE 'DeskPRO:emails_user:custom_%' OR name LIKE 'DeskPRO:emails_agent:custom_'");

		foreach ($custom_names as $id => $name) {
			$new_name = preg_replace('#^DeskPRO:emails_(user|agent):custom_(.*?)\.html\.twig$#', 'DeskPRO:emails_custom:$1_$2.html.twig', $name);
			$db->update('templates', array('name' => $new_name), array('id' => $id));
		}
	}
}