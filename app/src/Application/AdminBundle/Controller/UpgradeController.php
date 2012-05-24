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

class UpgradeController extends AbstractController
{
	public function startAction()
	{
		$waiting = $this->container->getSetting('core.upgrade_time');
		if ($waiting) {
			return $this->redirectRoute('admin_upgrade_watch');
		}

		$version_info = new \Orb\Util\OptionsArray(\Application\DeskPRO\Service\LicenseService::compareVersion());

		if ($this->in->getBool('start') && $version_info['count_behind']) {
			$mins = $this->in->getUint('minutes');
			if (!$mins) $mins = 0;

			$future = time() + $mins * 60;
			$this->container->getSettingsHandler()->setSetting('core.upgrade_time', $future);
			$this->container->getSettingsHandler()->setSetting('core.upgrade_set_at', time());
			$this->container->getSettingsHandler()->setSetting('core.upgrade_backup_files', $this->in->getInt('backup_files'));
			$this->container->getSettingsHandler()->setSetting('core.upgrade_backup_db', $this->in->getInt('backup_db'));
			$this->container->getSettingsHandler()->setSetting('core.upgrade_agent_notice', $this->in->getString('agent_message'));
			$this->container->getSettingsHandler()->setSetting('core.upgrade_user_notice', $this->in->getString('user_message'));

			if ($this->in->getString('agent_message')) {
				$agent_chat = new \Application\DeskPRO\Chat\AgentChat($this->person, $this->session->getEntity());
				$agent_ids = array_keys($this->em->getRepository('DeskPRO:Person')->getAgents());
				$agent_chat->sendAgentMessage($this->in->getString('agent_message'), $agent_ids, 0);
			}

			return $this->redirectRoute('admin_upgrade_watch');
		}

		return $this->render('AdminBundle:Upgrade:start.html.twig', array(
			'version_info' => $version_info,
		));
	}

	public function watchAction()
	{
		$waiting = $this->container->getSetting('core.upgrade_time');
		if (!$waiting) {
			return $this->redirectRoute('admin_upgrade');
		}

		return $this->render('AdminBundle:Upgrade:watch.html.twig');
	}

	public function stopAction()
	{
		$waiting = $this->container->getSetting('core.upgrade_time');
		if (!$waiting) {
			return $this->redirectRoute('admin_upgrade');
		}

		// To late now
		$file = @file_get_contents(DP_WEB_ROOT . ' /auto-update-status.txt');
		if ($file && strpos($file, 'STATUS(start)') !== null) {
			return $this->redirectRoute('admin_upgrade_watch');
		}

		$this->container->getSettingsHandler()->setSetting('core.upgrade_time', null);
		$this->container->getSettingsHandler()->setSetting('core.upgrade_set_at', null);
		$this->container->getSettingsHandler()->setSetting('core.upgrade_backup_files', null);
		$this->container->getSettingsHandler()->setSetting('core.upgrade_backup_db', null);
		$this->container->getSettingsHandler()->setSetting('core.upgrade_agent_notice', null);
		$this->container->getSettingsHandler()->setSetting('core.upgrade_user_notice', null);
		return $this->redirectRoute('admin_upgrade');
	}
}