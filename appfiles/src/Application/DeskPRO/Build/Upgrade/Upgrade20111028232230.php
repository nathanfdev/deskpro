<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20111028232230 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("CREATE TABLE article_category2usergroup (category_id INT NOT NULL, usergroup_id INT NOT NULL, INDEX IDX_6AD8B03212469DE2 (category_id), INDEX IDX_6AD8B032D2112630 (usergroup_id), PRIMARY KEY(category_id, usergroup_id)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE article_category2usergroup ADD FOREIGN KEY (category_id) REFERENCES article_categories(id) ON DELETE CASCADE");
			App::getDb()->exec("ALTER TABLE article_category2usergroup ADD FOREIGN KEY (usergroup_id) REFERENCES usergroups(id) ON DELETE CASCADE");
			App::getDb()->exec("CREATE TABLE feedback_category2usergroup (category_id INT NOT NULL, usergroup_id INT NOT NULL, INDEX IDX_878F350B12469DE2 (category_id), INDEX IDX_878F350BD2112630 (usergroup_id), PRIMARY KEY(category_id, usergroup_id)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE feedback_category2usergroup ADD FOREIGN KEY (category_id) REFERENCES feedback_categories(id) ON DELETE CASCADE");
			App::getDb()->exec("ALTER TABLE feedback_category2usergroup ADD FOREIGN KEY (usergroup_id) REFERENCES usergroups(id) ON DELETE CASCADE");
			App::getDb()->exec("CREATE TABLE news_category2usergroup (category_id INT NOT NULL, usergroup_id INT NOT NULL, INDEX IDX_6336075D12469DE2 (category_id), INDEX IDX_6336075DD2112630 (usergroup_id), PRIMARY KEY(category_id, usergroup_id)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE news_category2usergroup ADD FOREIGN KEY (category_id) REFERENCES news_categories(id) ON DELETE CASCADE");
			App::getDb()->exec("ALTER TABLE news_category2usergroup ADD FOREIGN KEY (usergroup_id) REFERENCES usergroups(id) ON DELETE CASCADE");
			App::getDb()->exec("CREATE TABLE download_category2usergroup (category_id INT NOT NULL, usergroup_id INT NOT NULL, INDEX IDX_53A2246F12469DE2 (category_id), INDEX IDX_53A2246FD2112630 (usergroup_id), PRIMARY KEY(category_id, usergroup_id)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE download_categories ADD FOREIGN KEY (parent_id) REFERENCES download_categories(id)");
			App::getDb()->exec("ALTER TABLE download_category2usergroup ADD FOREIGN KEY (category_id) REFERENCES download_categories(id) ON DELETE CASCADE");

			App::getDb()->exec("ALTER TABLE article_category_permissions ADD FOREIGN KEY (category_id) REFERENCES download_categories(id) ON DELETE CASCADE");
			App::getDb()->exec("ALTER TABLE feedback_category_permissions ADD FOREIGN KEY (category_id) REFERENCES download_categories(id) ON DELETE CASCADE");
			App::getDb()->exec("ALTER TABLE download_category_permissions ADD FOREIGN KEY (category_id) REFERENCES download_categories(id) ON DELETE CASCADE");
			App::getDb()->exec("ALTER TABLE news_category_permissions ADD FOREIGN KEY (category_id) REFERENCES download_categories(id) ON DELETE CASCADE");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
