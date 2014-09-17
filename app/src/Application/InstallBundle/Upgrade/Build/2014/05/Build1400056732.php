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

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Templating\Templates\TemplateSet;

class Build1400056732 extends AbstractBuild
{
	public function run()
	{
		$db = $this->container->getDb();

		$set = new TemplateSet(
			$this->container->getEm(),
			$this->container->get('twig'),
			$this->container->getSystemService('style')
		);

		#------------------------------
		# Rename templates
		#------------------------------

		$this->out("Renaming templates");

		$replacements = array(
			'DeskPRO:emails_user:new-reply-agent.html.twig' => 'DeskPRO:emails_user:ticket-reply-byagent.html.twig',
			'DeskPRO:emails_user:new-reply-user.html.twig'  => 'DeskPRO:emails_user:ticket-reply-autoreply.html.twig',
			'DeskPRO:emails_user:new-ticket.html.twig'      => 'DeskPRO:emails_user:ticket-new-autoreply.html.twig',
		);

		foreach ($replacements as $oldname => $newname) {
			$this->out("Rename $oldname -> $newname");
			$db->update('templates', array('name' => $newname), array('name' => $oldname));
		}

		#------------------------------
		# Rename custom
		#------------------------------

		$this->out("Renaming custom templates");

		$custom_names = $db->fetchAllKeyValue("SELECT id, name FROM templates WHERE name LIKE 'DeskPRO:emails_user:custom_%' OR name LIKE 'DeskPRO:emails_agent:custom_%'");

		foreach ($custom_names as $id => $name) {
			$new_name = preg_replace('#^DeskPRO:emails_(user|agent):custom_(.*?)\.html\.twig$#', 'DeskPRO:emails_custom:$1_$2.html.twig', $name);
			$db->update('templates', array('name' => $new_name), array('id' => $id));
		}

		#------------------------------
		# Copy old default templates into custom ones
		#------------------------------

		$this->out("Copying old default templates into custom template");

		$copy_list = array(
			'DeskPRO:emails_user:ticket-autoclose-warn.html.twig' => array(
				'file'     => DP_ROOT.'/src/Application/DeskPRO/Resources/views/emails_user/ticket-autoclose-warn.html.twig',
				'new_name' => 'DeskPRO:emails_custom:user_autoclose_warn.html.twig'
			),
		);

		foreach ($copy_list as $old_name => $info) {
			$db->delete('templates', array('name' => $old_name));
			$this->out("Saving $old_name to {$info['new_name']}");

			try {
				$code = file_get_contents($info['file']);
				$code = $code;
				$code = str_replace(array_keys($replacements), array_values($replacements), $code);

				$template      = $set->getCustomTemplate($info['new_name']);
				$template_code = $template->getTemplateCode();
				$template_code->setCode($code);
				$set->saveTemplate($template);
			} catch (\Exception $e) {
				$this->out("... Failed: {$e->getMessage()}");
			}
		}

		#------------------------------
		# Recompile templates
		#------------------------------

		$this->out("Re-compiling custom templates");

		$tids = $db->fetchAllCol("SELECT id FROM templates");
		$failed = array();
		$failed_data = array();

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
			$db->executeUpdate("DELETE FROM templates WHERE id IN (?)", array($failed), array(Connection::PARAM_INT_ARRAY));
		}

		#------------------------------
		# Process template names in actions
		#------------------------------

		$proc_actions = function($actions) use ($copy_list) {
			$actions = @json_decode($actions, true);
			if (!$actions || empty($actions['@DATA']['actions'])) return null;

			$did = false;
			foreach ($actions['@DATA']['actions'] as &$act) {
				switch ($act['type']) {
					case 'SendAgentEmail':
					case 'SendUserEmail':
						$tpl = @$act['options']['template'];
						if ($tpl && isset($copy_list[$tpl])) {
							$did = true;
							$act['options']['template'] = $copy_list[$tpl]['new_name'];
						}
						break;
				}
			}

			if ($did) {
				return json_encode($actions);
			}

			return null;
		};

		foreach ($db->fetchAll("SELECT id, actions FROM ticket_triggers") as $x) {
			$actions = $proc_actions($x['actions']);
			if ($actions) {
				$db->update('ticket_triggers', array('actions' => $actions), array('id' => $x['id']));
			}
		}
		foreach ($db->fetchAll("SELECT id, actions FROM ticket_escalations") as $x) {
			$actions = $proc_actions($x['actions']);
			if ($actions) {
				$db->update('ticket_escalations', array('actions' => $actions), array('id' => $x['id']));
			}
		}
	}
}