<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110317112317 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add missing Twitter tables/alterations');

		try {
            App::getDb()->exec("CREATE TABLE IF NOT EXISTS twitter_accounts_following (id BIGINT AUTO_INCREMENT NOT NULL, account_id BIGINT DEFAULT NULL, user_id BIGINT DEFAULT NULL, INDEX twitter_accounts_following_account_id_idx (account_id), INDEX twitter_accounts_following_user_id_idx (user_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE IF NOT EXISTS twitter_accounts_person (account_id BIGINT NOT NULL, user_id BIGINT NOT NULL, PRIMARY KEY(account_id, user_id)) ENGINE = InnoDB");
			//App::getDb()->exec("ALTER TABLE twitter_accounts ADD user_id BIGINT NOT NULL, ADD UNIQUE (user_id), ADD FOREIGN KEY (user_id) REFERENCES twitter_users (id)");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
