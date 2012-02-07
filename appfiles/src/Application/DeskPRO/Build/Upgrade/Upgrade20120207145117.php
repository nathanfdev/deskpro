<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20120207145117 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			$sql = <<<SQL
RENAME TABLE `custom_data_idea` TO `custom_data_feedback`;
RENAME TABLE `custom_def_idea` TO `custom_def_feedback`;
RENAME TABLE `idea_categories` TO `feedback_categories`;
RENAME TABLE `idea_category_permissions` TO `feedback_category_permissions`;
RENAME TABLE `idea_category2usergroup` TO `feedback_category2usergroup`;
RENAME TABLE `idea_comments` TO `feedback_comments`;
RENAME TABLE `idea_revisions` TO `feedback_revisions`;
RENAME TABLE `idea_status_categories` TO `feedback_status_categories`;
RENAME TABLE `idea_votes` TO `feedback_votes`;
RENAME TABLE `ideas` TO `feedback`;
RENAME TABLE `labels_ideas` TO `labels_feedback`;
SQL;

			$queries = explode(';', $sql);
			foreach ($queries as $q) {
				App::getDb()->exec($q);
			}

		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
