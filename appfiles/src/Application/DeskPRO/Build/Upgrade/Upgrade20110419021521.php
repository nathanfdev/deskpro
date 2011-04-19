<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110419021521 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Recreate usergroups');

		try {
			App::getDb()->exec("DROP TABLE IF EXISTS person2usergroups");
			App::getDb()->exec("DROP TABLE IF EXISTS UsergroupPropertyPermission");
			App::getDb()->exec("DROP TABLE IF EXISTS usergroup_properties");
			App::getDb()->exec("DROP TABLE IF EXISTS usergroups");

			App::getDb()->exec("CREATE TABLE usergroups (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, note LONGTEXT NOT NULL, is_agent_group TINYINT(1) NOT NULL, sys_name VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE usergroup_properties (id INT AUTO_INCREMENT NOT NULL, usergroup_id INT DEFAULT NULL, name VARCHAR(50) NOT NULL, flag TINYINT(1) DEFAULT NULL, data LONGTEXT DEFAULT NULL, property_type VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_3F09DB16D2112630 (usergroup_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE person2usergroups (person_id INT NOT NULL, usergroup_id INT NOT NULL, INDEX IDX_356C969E217BBB47 (person_id), INDEX IDX_356C969ED2112630 (usergroup_id), PRIMARY KEY(person_id, usergroup_id)) ENGINE = InnoDB");

			App::getDb()->insert('usergroups', array(
				'id'             => 1,
				'title'          => 'Everyone',
				'note'           => 'Every user',
				'is_agent_group' => 0,
				'sys_name'       => 'everyone'
			));

			App::getDb()->exec("CREATE TABLE article_category_permissions (usergroup_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_E65C1B50D2112630 (usergroup_id), INDEX IDX_E65C1B5012469DE2 (category_id), PRIMARY KEY(usergroup_id, category_id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE permissions_cache (name VARCHAR(255) NOT NULL, usergroup_key VARCHAR(32) NOT NULL, usergroup_ids VARCHAR(1000) NOT NULL, perms LONGTEXT NOT NULL COMMENT '(DC2Type:object)', PRIMARY KEY(name, usergroup_key)) ENGINE = InnoDB");

		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
