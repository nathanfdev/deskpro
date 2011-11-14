<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20111110104200 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("DROP TABLE IF EXISTS `StyleResourceCss`");
			App::getDb()->exec("DROP TABLE IF EXISTS `styles`");
			App::getDb()->exec("CREATE TABLE styles (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, note LONGTEXT NOT NULL, css_dir VARCHAR(255) NOT NULL, css_updated DATETIME NOT NULL, options LONGTEXT NOT NULL COMMENT '(DC2Type:array)', created_at DATETIME NOT NULL, INDEX IDX_B65AFAF5727ACA70 (parent_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("INSERT INTO `styles` (`id`, `parent_id`, `title`, `note`, `options`, `css_dir`, `css_updated`, `created_at`) VALUES (NULL, NULL, 'Default', '', '".serialize(array())."', 'stylesheets/user', NOW(), NOW())");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
