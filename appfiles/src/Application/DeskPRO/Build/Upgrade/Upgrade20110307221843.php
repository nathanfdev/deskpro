<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110307221843 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Twitter tables');

		try {
			App::getDb()->exec("CREATE TABLE IF NOT EXISTS twitter_accounts (id BIGINT NOT NULL, user_id BIGINT DEFAULT NULL, INDEX twitter_accounts_user_id_idx (user_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE IF NOT EXISTS twitter_accounts_person (account_id BIGINT NOT NULL, person_id INT NOT NULL, INDEX twitter_accounts_person_account_id_idx (account_id), INDEX twitter_accounts_person_person_id_idx (person_id), PRIMARY KEY(account_id, person_id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE IF NOT EXISTS twitter_accounts_followers (id BIGINT AUTO_INCREMENT NOT NULL, account_id BIGINT DEFAULT NULL, user_id BIGINT DEFAULT NULL, INDEX twitter_accounts_followers_account_id_idx (account_id), INDEX twitter_accounts_followers_user_id_idx (user_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE IF NOT EXISTS twitter_accounts_searches (id BIGINT AUTO_INCREMENT NOT NULL, account_id BIGINT DEFAULT NULL, term VARCHAR(255) NOT NULL, INDEX twitter_accounts_searches_account_id_idx (account_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE IF NOT EXISTS twitter_statuses (id BIGINT NOT NULL, user_id BIGINT DEFAULT NULL, in_reply_to_status_id BIGINT DEFAULT NULL, in_reply_to_user_id BIGINT DEFAULT NULL, recipient_id BIGINT DEFAULT NULL, text VARCHAR(4000) NOT NULL, is_truncated TINYINT(1) NOT NULL, is_favorited TINYINT(1) NOT NULL, is_archived TINYINT(1) NOT NULL, date_created DATETIME NOT NULL, geo_latitude NUMERIC(10, 5) DEFAULT NULL, geo_longitude NUMERIC(10, 5) DEFAULT NULL, source VARCHAR(4000) DEFAULT NULL, INDEX twitter_statuses_user_id_idx (user_id), INDEX twitter_statuses_in_reply_to_status_id_idx (in_reply_to_status_id), INDEX twitter_statuses_in_reply_to_user_id_idx (in_reply_to_user_id), INDEX twitter_statuses_recipient_id_idx (recipient_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE IF NOT EXISTS twitter_statuses_long (id BIGINT AUTO_INCREMENT NOT NULL, status_id BIGINT DEFAULT NULL, text VARCHAR(4000) NOT NULL, is_public TINYINT(1) NOT NULL, date_created DATETIME NOT NULL, is_read TINYINT(1) NOT NULL, date_read DATETIME DEFAULT NULL, UNIQUE INDEX twitter_statuses_long_status_id_uniq (status_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE IF NOT EXISTS twitter_statuses_mentions (id BIGINT AUTO_INCREMENT NOT NULL, status_id BIGINT DEFAULT NULL, user_id BIGINT DEFAULT NULL, starts INT NOT NULL, ends INT NOT NULL, INDEX twitter_statuses_mentions_status_id_idx (status_id), INDEX twitter_statuses_mentions_user_id_idx (user_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE IF NOT EXISTS twitter_statuses_tags (id BIGINT AUTO_INCREMENT NOT NULL, status_id BIGINT DEFAULT NULL, hash VARCHAR(255) NOT NULL, starts INT NOT NULL, ends INT NOT NULL, INDEX twitter_statuses_tags_status_id_idx (status_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE IF NOT EXISTS twitter_statuses_urls (id BIGINT AUTO_INCREMENT NOT NULL, status_id BIGINT DEFAULT NULL, url VARCHAR(255) NOT NULL, starts INT NOT NULL, ends INT NOT NULL, INDEX twitter_statuses_urls_status_id_idx (status_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE IF NOT EXISTS twitter_users (id BIGINT NOT NULL, name VARCHAR(40) NOT NULL, screen_name VARCHAR(20) NOT NULL, profile_image_url VARCHAR(200) NOT NULL, language VARCHAR(3) NOT NULL, is_protected TINYINT(1) NOT NULL, is_verified TINYINT(1) NOT NULL, location VARCHAR(255) NOT NULL, is_geo_enabled TINYINT(1) NOT NULL, geo_latitude NUMERIC(10, 5) DEFAULT NULL, geo_longitude NUMERIC(10, 5) DEFAULT NULL, PRIMARY KEY(id)) ENGINE = InnoDB");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
