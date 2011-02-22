<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110221232853 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Drop old tables');

		try {
			App::getDb()->exec("DROP TABLE IF EXISTS `style_resources`");
			App::getDb()->exec("DROP TABLE IF EXISTS `templates`");
			App::getDb()->exec("DROP TABLE IF EXISTS `styles`");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}

	public function step2()
	{
		$this->output->writeln('Recreate tables');

		try {
			App::getDb()->exec("CREATE TABLE styles (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, note LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX styles_parent_id_idx (parent_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE templates (id INT AUTO_INCREMENT NOT NULL, style_id INT DEFAULT NULL, path VARCHAR(255) NOT NULL, template LONGTEXT NOT NULL, template_compiled LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX templates_style_id_idx (style_id), PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}

	public function step3()
	{
		$this->output->writeln('Insert default style');

		try {
			App::getDb()->exec("CREATE TABLE styles (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, note LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX styles_parent_id_idx (parent_id), PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
