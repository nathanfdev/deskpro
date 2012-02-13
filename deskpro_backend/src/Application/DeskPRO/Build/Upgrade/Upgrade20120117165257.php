<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20120117165257 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("DROP TABLE IF EXISTS portal_page_display");
			App::getDb()->exec("CREATE TABLE portal_page_display (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, display_order INT NOT NULL, is_enabled TINYINT(1) NOT NULL, section VARCHAR(50) NOT NULL, data LONGTEXT NOT NULL COMMENT '(DC2Type:array)', PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}

	public function step2()
	{
		$this->output->writeln('Recreating portal blocks');

		try {
			// Install data stuff
			$install_data = new \Application\InstallBundle\Install\InstallDataReader(DP_ROOT.'/src/Application/InstallBundle/Data/data.php');
			$em = App::getOrm();

			$em->beginTransaction();
			foreach ($install_data->getAllForTag('create_portal_block') as $php) {
				eval($php);
			}

			$em->flush();
			$em->commit();
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
