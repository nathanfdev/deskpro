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

namespace Application\DeskPRO\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Symfony\Bundle\DoctrineBundle\Command\Proxy\DoctrineCommandHelper;
use Symfony\Bundle\DoctrineMigrationsBundle\Command\DoctrineCommand;

use Doctrine\DBAL\Migrations\Configuration\Configuration;

use Doctrine\ORM\Tools\SchemaTool;

use Orb\Util\Util;
use Orb\Util\Numbers;

use Application\DeskPRO\App;

class DevDoMigrationCommand extends \Symfony\Bundle\DoctrineMigrationsBundle\Command\MigrationsMigrateDoctrineCommand
{
	protected function configure()
	{
		parent::configure();
		$this->setName('dpdev:do-migration');
	}

	public function execute(InputInterface $input, OutputInterface $output)
	{
		parent::execute($input, $output);

		$version = App::getDb()->fetchColumn("SELECT version FROM dev_migration_versions ORDER BY version DESC LIMIT 1");
		if (!$version) {
			$version = date('YmdHis');
		}

		// Update version setting
		App::getDb()->replace('settings', array(
			'name' => 'core.deskpro_version',
			'groupname' => 'core',
			'value' => $version,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		));
	}

}