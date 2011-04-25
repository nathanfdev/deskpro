<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110425214614 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add other permission tables');

		try {
			App::getDb()->exec("CREATE TABLE download_category_permissions (usergroup_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_1E2B566ED2112630 (usergroup_id), INDEX IDX_1E2B566E12469DE2 (category_id), PRIMARY KEY(usergroup_id, category_id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE idea_category_permissions (usergroup_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_750A0C12D2112630 (usergroup_id), INDEX IDX_750A0C1212469DE2 (category_id), PRIMARY KEY(usergroup_id, category_id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE news_category_permissions (usergroup_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_513C43F9D2112630 (usergroup_id), INDEX IDX_513C43F912469DE2 (category_id), PRIMARY KEY(usergroup_id, category_id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
