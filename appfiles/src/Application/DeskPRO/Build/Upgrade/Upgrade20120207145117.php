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

			$tables = array(
				'custom_data_idea',
				'custom_def_idea',
				'idea_categories',
				'idea_category_permissions',
				'idea_category2usergroup',
				'idea_comments',
				'idea_revisions',
				'idea_status_categories',
				'idea_votes',
				'ideas',
				'labels_ideas'
			);

			App::getDb()->exec("SET FOREIGN_KEY_CHECKS = 0");
			foreach ($tables as $t) {
				App::getDb()->exec("DROP TABLE IF EXISTS $t");

				$t = str_replace(array('ideas', 'idea'), 'feedback', $t);
				App::getDb()->exec("DROP TABLE IF EXISTS $t");
			}

			App::getDb()->exec("CREATE TABLE feedback (id INT AUTO_INCREMENT NOT NULL, hidden_status VARCHAR(15) DEFAULT NULL, validating VARCHAR(35) DEFAULT NULL, popularity INT NOT NULL, slug VARCHAR(100) NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, view_count INT NOT NULL, total_rating INT NOT NULL, num_ratings INT NOT NULL, status VARCHAR(15) NOT NULL, date_created DATETIME NOT NULL, date_published DATETIME DEFAULT NULL, status_category_id INT DEFAULT NULL, category_id INT DEFAULT NULL, person_id INT DEFAULT NULL, language_id INT DEFAULT NULL, INDEX IDX_D2294458169CE813 (status_category_id), INDEX IDX_D229445812469DE2 (category_id), INDEX IDX_D2294458217BBB47 (person_id), INDEX IDX_D229445882F1BAF4 (language_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE feedback_status_categories (id INT AUTO_INCREMENT NOT NULL, status_type VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, display_order INT NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE feedback_categories (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, display_order INT NOT NULL, depth INT NOT NULL, root INT DEFAULT NULL, INDEX IDX_66FE6832727ACA70 (parent_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE feedback_revisions (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, status VARCHAR(30) NOT NULL, date_created DATETIME NOT NULL, feedback_id INT DEFAULT NULL, person_id INT DEFAULT NULL, INDEX IDX_37F57C3ED249A887 (feedback_id), INDEX IDX_37F57C3E217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE feedback_comments (id INT AUTO_INCREMENT NOT NULL, ip_address VARCHAR(30) NOT NULL, email VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, content LONGTEXT NOT NULL, status VARCHAR(30) NOT NULL, validating VARCHAR(35) DEFAULT NULL, is_reviewed TINYINT(1) NOT NULL, date_created DATETIME NOT NULL, feedback_id INT DEFAULT NULL, person_id INT DEFAULT NULL, visitor_id INT DEFAULT NULL, INDEX IDX_10D03D58D249A887 (feedback_id), INDEX IDX_10D03D58217BBB47 (person_id), INDEX IDX_10D03D5870BEE6D (visitor_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE custom_data_feedback (feedback_id INT NOT NULL, field_id INT NOT NULL, value INT NOT NULL, input LONGTEXT NOT NULL, INDEX IDX_92E9C37FD249A887 (feedback_id), INDEX IDX_92E9C37F443707B0 (field_id), INDEX field_id_idx (field_id, feedback_id), PRIMARY KEY(feedback_id, field_id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE custom_def_feedback (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, js_class VARCHAR(255) NOT NULL, has_form_template TINYINT(1) NOT NULL, has_display_template TINYINT(1) NOT NULL, title VARCHAR(255) NOT NULL, handler_class VARCHAR(255) DEFAULT NULL, options LONGTEXT NOT NULL COMMENT '(DC2Type:array)', is_user_enabled TINYINT(1) NOT NULL, is_enabled TINYINT(1) NOT NULL, display_order INT NOT NULL, plugin_id VARCHAR(255) DEFAULT NULL, INDEX IDX_CC9CDDD8727ACA70 (parent_id), UNIQUE INDEX UNIQ_CC9CDDD8EC942BCF (plugin_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE labels_feedback (feedback_id INT NOT NULL, `label` VARCHAR(255) NOT NULL, INDEX IDX_42C4DA40D249A887 (feedback_id), PRIMARY KEY(feedback_id, label)) ENGINE = InnoDB");

			App::getDb()->exec("CREATE TABLE feedback_category2usergroup (category_id INT NOT NULL, usergroup_id INT NOT NULL, INDEX IDX_B304B93C12469DE2 (category_id), INDEX IDX_B304B93CD2112630 (usergroup_id), PRIMARY KEY(category_id, usergroup_id)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE feedback_categories ADD CONSTRAINT FK_66FE6832727ACA70 FOREIGN KEY (parent_id) REFERENCES feedback_categories (id)");
			App::getDb()->exec("ALTER TABLE feedback_category2usergroup ADD CONSTRAINT FK_B304B93C12469DE2 FOREIGN KEY (category_id) REFERENCES feedback_categories (id) ON DELETE CASCADE");
			App::getDb()->exec("ALTER TABLE custom_def_feedback ADD CONSTRAINT FK_CC9CDDD8727ACA70 FOREIGN KEY (parent_id) REFERENCES custom_def_feedback (id) ON DELETE CASCADE");

			App::getDb()->exec("
				UPDATE portal_page_display SET `type` = 'Feedback' WHERE `type` = 'Ideas'
			");

			App::getDb()->exec("SET FOREIGN_KEY_CHECKS = 1");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
