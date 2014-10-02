<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

class Build1400056726 extends AbstractBuild
{
	public function run()
	{
		$db = $this->container->getDb();

		$this->out("Updating registration settings");

		$settings = array(
			'core.reg_enabled'      => 0,
			'core.reg_required'     => 0,
			'core.agent_validation' => 0,
		);

		$mode = $db->fetchColumn("SELECT value FROM settings WHERE name = 'core.user_mode'");

		switch ($mode) {
			case 'open':
				$settings['core.reg_enabled']      = 1;
				$settings['core.reg_required']     = 0;
				$settings['core.agent_validation'] = 0;
				break;

			case 'require_reg':
				$settings['core.reg_enabled']      = 1;
				$settings['core.reg_required']     = 1;
				$settings['core.agent_validation'] = 0;
				break;

			case 'require_reg_agent_validation':
				$settings['core.reg_enabled']      = 1;
				$settings['core.reg_required']     = 1;
				$settings['core.agent_validation'] = 1;
				break;

			case 'closed':
				$settings['core.reg_enabled']      = 0;
				$settings['core.reg_required']     = 0;
				$settings['core.agent_validation'] = 0;
				break;

			default:
				$settings['core.reg_enabled']      = 1;
				$settings['core.reg_required']     = 0;
				$settings['core.agent_validation'] = 0;
				break;
		}

		foreach ($settings as $k => $v) {
			$db->replace('settings', array('name' => $k, 'value' => $v));
		}

		$db->delete('settings', array('name' => 'core.user_mode'));
	}
}