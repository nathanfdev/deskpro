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
 * @category Entities
 */

namespace deskpro_hipchat;

use Application\DeskPRO\App\Native\InstallerHandler\InstallerContext;
use Application\DeskPRO\App\Native\InstallerHandler\AbstractInstallerHandler;

class InstallerHandler extends AbstractInstallerHandler
{
	/**
	 * {@inheritDoc}
	 */
	public function install(InstallerContext $context)
	{
		$this->refreshTriggerAction($context);
	}


	/**
	 * {@inheritDoc}
	 */
	public function uninstall(InstallerContext $context)
	{
		$action_name = $this->getActionName($context);
		$context->getDb()->executeUpdate("DELETE FROM ticket_actions_def WHERE action_name = ?", array($action_name));
	}


	/**
	 * {@inheritDoc}
	 */
	public function updateSettings(InstallerContext $context)
	{
		$this->refreshTriggerAction($context);
	}


	/**
	 * {@inheritDoc}
	 */
	public function updatePackage(InstallerContext $context)
	{
		$this->refreshTriggerAction($context);
	}


	/**
	 * @param InstallerContext $context
	 */
	private function refreshTriggerAction(InstallerContext $context)
	{
		$action_name = $this->getActionName($context);
		$rec = array(
			'app_id'      => $context->getApp()->id,
			'action_name' => $action_name,
			'def_class'   => 'deskpro_hipchat\\Ticket\\Actions\\ActionDef\\HipChatActionDef',
			'settings'    => null
		);

		$exist_id = $context->getDb()->fetchColumn("SELECT id FROM ticket_actions_def WHERE action_name = ?", array($action_name));
		if ($exist_id) {
			$context->getDb()->update('ticket_actions_def', $rec, array('id' => $exist_id));
		} else {
			$context->getDb()->insert('ticket_actions_def', $rec);
		}
	}


	/**
	 * @param InstallerContext $context
	 *
	 * @return string
	 */
	private function getActionName(InstallerContext $context)
	{
		$action_name = 'HipChatAction'.$context->getApp()->id;

		return $action_name;
	}
}
