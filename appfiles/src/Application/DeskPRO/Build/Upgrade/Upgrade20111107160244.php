<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20111107160244 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("CREATE TABLE custom_def_article (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, plugin_id VARCHAR(255) DEFAULT NULL, js_class VARCHAR(255) NOT NULL, has_form_template TINYINT(1) NOT NULL, has_display_template TINYINT(1) NOT NULL, title VARCHAR(255) NOT NULL, handler_class VARCHAR(255) DEFAULT NULL, options LONGTEXT NOT NULL COMMENT '(DC2Type:array)', is_user_enabled TINYINT(1) NOT NULL, is_enabled TINYINT(1) NOT NULL, display_order INT NOT NULL, INDEX IDX_B651E6F4727ACA70 (parent_id), UNIQUE INDEX UNIQ_B651E6F4EC942BCF (plugin_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE custom_def_article ADD CONSTRAINT FK_B651E6F4727ACA70 FOREIGN KEY (parent_id) REFERENCES custom_def_article(id) ON DELETE CASCADE");
			App::getDb()->exec("ALTER TABLE custom_def_article ADD CONSTRAINT FK_B651E6F4EC942BCF FOREIGN KEY (plugin_id) REFERENCES plugins(id) ON DELETE SET NULL");
			App::getDb()->exec("CREATE TABLE custom_data_article (article_id INT NOT NULL, field_id INT NOT NULL, value INT NOT NULL, input LONGTEXT NOT NULL, INDEX IDX_1DB64F8C7294869C (article_id), INDEX IDX_1DB64F8C443707B0 (field_id), INDEX field_id_idx (field_id, article_id), PRIMARY KEY(article_id, field_id)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE custom_data_article ADD CONSTRAINT FK_1DB64F8C7294869C FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE");
			App::getDb()->exec("ALTER TABLE custom_data_article ADD CONSTRAINT FK_1DB64F8C443707B0 FOREIGN KEY (field_id) REFERENCES custom_def_article(id) ON DELETE CASCADE");
			App::getDb()->exec("
				INSERT INTO `custom_def_article` (`js_class`, `parent_id`, `title`, `handler_class`, `options`, `plugin_id`, `has_form_template`, `has_display_template`, `is_enabled`, `is_user_enabled`, `display_order`) VALUES
				('', NULL, 'Example Field', 'Application\\\\DeskPRO\\\\CustomFields\\\\Handler\\\\Text', 'a:2:{s:10:\"min_length\";N;s:10:\"max_length\";N;}', NULL, 1, 1, 1, 1, 0);
			");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
