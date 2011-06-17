<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110617050630 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('My upgrade step');

		try {
			App::getDb()->exec("INSERT INTO `ideas` (`id`, `person_id`, `status_category_id`, `category_id`, `first_comment_id`, `title`, `status`, `hidden_status`, `num_votes`, `date_created`) VALUES(1, 20001, 1, 1, 15, 'Idea 1', 'active', NULL, 0, '2011-06-17 04:56:02')");
			App::getDb()->exec("INSERT INTO `ideas` (`id`, `person_id`, `status_category_id`, `category_id`, `first_comment_id`, `title`, `status`, `hidden_status`, `num_votes`, `date_created`) VALUES(2, 20001, 2, 1, 16, 'Idea 2', 'active', NULL, 0, '2011-06-17 04:56:02')");
			App::getDb()->exec("INSERT INTO `ideas` (`id`, `person_id`, `status_category_id`, `category_id`, `first_comment_id`, `title`, `status`, `hidden_status`, `num_votes`, `date_created`) VALUES(3, 20001, 4, 3, 17, 'Idea 3', 'closed', NULL, 0, '2011-06-17 04:56:02')");
			App::getDb()->exec("INSERT INTO `ideas` (`id`, `person_id`, `status_category_id`, `category_id`, `first_comment_id`, `title`, `status`, `hidden_status`, `num_votes`, `date_created`) VALUES(4, 20001, 5, 4, 18, 'Idea 4', 'closed', NULL, 0, '2011-06-17 04:56:02')");
			App::getDb()->exec("INSERT INTO `ideas` (`id`, `person_id`, `status_category_id`, `category_id`, `first_comment_id`, `title`, `status`, `hidden_status`, `num_votes`, `date_created`) VALUES(5, 20001, NULL, 4, 19, 'Idea 5', 'spam', NULL, 0, '2011-06-17 04:56:02')");
			App::getDb()->exec("INSERT INTO `ideas` (`id`, `person_id`, `status_category_id`, `category_id`, `first_comment_id`, `title`, `status`, `hidden_status`, `num_votes`, `date_created`) VALUES(6, 20001, NULL, 4, 20, 'Idea 6', 'deleted', NULL, 0, '2011-06-17 04:56:02')");
			App::getDb()->exec("INSERT INTO `ideas` (`id`, `person_id`, `status_category_id`, `category_id`, `first_comment_id`, `title`, `status`, `hidden_status`, `num_votes`, `date_created`) VALUES(7, 20001, NULL, 4, 21, 'Idea 7', 'hidden', 'validating', 0, '2011-06-17 04:56:02')");
			App::getDb()->exec("INSERT INTO `idea_comments` (`id`, `idea_id`, `person_id`, `visitor_id`, `ip_address`, `email`, `name`, `content`, `status`, `date_created`) VALUES(15, 1, 20001, NULL, '', NULL, NULL, 'This is my idea 1', 'visible', '2011-06-17 04:56:02')");
			App::getDb()->exec("INSERT INTO `idea_comments` (`id`, `idea_id`, `person_id`, `visitor_id`, `ip_address`, `email`, `name`, `content`, `status`, `date_created`) VALUES(16, 2, 20001, NULL, '', NULL, NULL, 'This is my idea 2', 'visible', '2011-06-17 04:56:02')");
			App::getDb()->exec("INSERT INTO `idea_comments` (`id`, `idea_id`, `person_id`, `visitor_id`, `ip_address`, `email`, `name`, `content`, `status`, `date_created`) VALUES(17, 3, 20001, NULL, '', NULL, NULL, 'This is my idea 3', 'visible', '2011-06-17 04:56:02')");
			App::getDb()->exec("INSERT INTO `idea_comments` (`id`, `idea_id`, `person_id`, `visitor_id`, `ip_address`, `email`, `name`, `content`, `status`, `date_created`) VALUES(18, 4, 20001, NULL, '', NULL, NULL, 'This is my idea 4', 'visible', '2011-06-17 04:56:02')");
			App::getDb()->exec("INSERT INTO `idea_comments` (`id`, `idea_id`, `person_id`, `visitor_id`, `ip_address`, `email`, `name`, `content`, `status`, `date_created`) VALUES(19, 5, 20001, NULL, '', NULL, NULL, 'This is my idea 5', 'visible', '2011-06-17 04:56:02')");
			App::getDb()->exec("INSERT INTO `idea_comments` (`id`, `idea_id`, `person_id`, `visitor_id`, `ip_address`, `email`, `name`, `content`, `status`, `date_created`) VALUES(20, 6, 20001, NULL, '', NULL, NULL, 'This is my idea 6', 'visible', '2011-06-17 04:56:02')");
			App::getDb()->exec("INSERT INTO `idea_comments` (`id`, `idea_id`, `person_id`, `visitor_id`, `ip_address`, `email`, `name`, `content`, `status`, `date_created`) VALUES(21, 7, 20001, NULL, '', NULL, NULL, 'This is my idea 7', 'visible', '2011-06-17 04:56:02')");
			App::getDb()->exec("INSERT INTO `labels_ideas` (`idea_id`, `label`) VALUES (1, 'test')");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
