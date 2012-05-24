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

namespace Application\DeskPRO\Command;

namespace Application\DeskPRO\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Strings;

class InternalUpgradeRunnerCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('dp:internal-upgrade-runner');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		if (!$this->getContainer()->getPhpBinaryPath()) {
			$output->write('<error>Could not find path to PHP</error>');
			return;
		}

		$cmd = "\"" . $this->getContainer()->getPhpBinaryPath() . "\"";
		$cmd .= " \"" . DP_ROOT.'/bin/upgrade-util.php' . "\"";
		$cmd .= ' --auto --quiet --write-status-file';

		if (!$this->getContainer()->getSetting('core.upgrade_backup_files')) {
			$cmd .= ' --skip-backup-file';
		}
		if (!$this->getContainer()->getSetting('core.upgrade_backup_db')) {
			$cmd .= ' --skip-backup-db ';
		}

		$this->getContainer()->getSettingsHandler()->setSetting('core.upgrade_time', null);
		$this->getContainer()->getSettingsHandler()->setSetting('core.upgrade_set_at', null);
		$this->getContainer()->getSettingsHandler()->setSetting('core.upgrade_backup_files', null);
		$this->getContainer()->getSettingsHandler()->setSetting('core.upgrade_backup_db', null);
		$this->getContainer()->getSettingsHandler()->setSetting('core.upgrade_agent_notice', null);
		$this->getContainer()->getSettingsHandler()->setSetting('core.upgrade_user_notice', null);

		$ret = null;
		passthru($cmd, $ret);

		$this->getContainer()->getSettingsHandler()->setSetting('core.last_auto_upgrade_time', time());

		return $ret;
	}
}