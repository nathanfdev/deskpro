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
		if (file_exists(DP_WEB_ROOT . '/auto-update-status.txt')) {
			@unlink(DP_WEB_ROOT . '/auto-update-status.txt');
		}
		$write_status = function($code, $message = '') {
			$fp = fopen(DP_WEB_ROOT . '/auto-update-status.txt', 'a');
			$time = time();

			if (is_array($message)) {
				$message = json_encode($message);
			}

			fwrite($fp, "STATUS(" . $code . ")@$time#$message\n");
			fclose($fp);
		};

		$skip_seg = '';
		if (!$this->getContainer()->getSetting('core.upgrade_backup_files')) {
			$skip_seg .= ' --skip-backup-file';
		}
		if (!$this->getContainer()->getSetting('core.upgrade_backup_db')) {
			$skip_seg .= ' --skip-backup-db ';
		}

		$this->getContainer()->getSettingsHandler()->setSetting('core.upgrade_time', null);
		$this->getContainer()->getSettingsHandler()->setSetting('core.upgrade_set_at', null);
		$this->getContainer()->getSettingsHandler()->setSetting('core.upgrade_backup_files', null);
		$this->getContainer()->getSettingsHandler()->setSetting('core.upgrade_backup_db', null);

		if (!dp_get_php_path(true)) {
			$write_status('error_php_path');
			$write_status("error_unknown_binary", array('php'));
			$write_status("error_basic_checks_fail");
			$output->write('<error>Could not find path to PHP</error>');
			return 1;
		}

		$cmd = sprintf(
			"%s %s --auto --quiet --write-status-file %s",
			dp_get_php_path(),
			escapeshellarg(DP_ROOT.'/bin/upgrade-util.php'),
			$skip_seg
		);

		set_time_limit(0);
		$ret = null;
		exec($cmd, $out, $ret);

		$str = implode("\n", $out);
		if ($str) {
			echo $str;
		}

		$this->getContainer()->getSettingsHandler()->setSetting('core.last_auto_upgrade_time', time());

		return $ret;
	}
}