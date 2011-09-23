<?php

namespace Application\DeskPRO\Build\Upgrade;

use Application\DeskPRO\App;
use Application\DeskPRO\Build\Upgrader;

class Upgrade20110808162732 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Drop old tables');

		try {
			App::getDb()->exec("DROP TABLE IF EXISTS ticket_snippets");
			App::getDb()->exec("DROP TABLE IF EXISTS ticket_snippet_categories");
			App::getDb()->exec("DROP TABLE IF EXISTS ticket_snippet_to_team");
			App::getDb()->exec("DROP TABLE IF EXISTS ticket_snippetcat_to_team");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		$this->output->writeln('Recreate tables');

		try {
			App::getDb()->exec("CREATE TABLE ticket_snippet_categories (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, is_global TINYINT(1) NOT NULL, title VARCHAR(255) NOT NULL, INDEX IDX_141D3B01217BBB47 (person_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE ticket_snippets (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, category_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, snippet LONGTEXT NOT NULL, INDEX IDX_6848095D217BBB47 (person_id), INDEX IDX_6848095D12469DE2 (category_id), PRIMARY KEY(id)) ENGINE = InnoDB");
			App::getDb()->exec("CREATE TABLE ticket_snippetcat_to_team (team_id INT NOT NULL, INDEX IDX_4E1AB897296CD8AE (team_id), PRIMARY KEY(team_id)) ENGINE = InnoDB");
			App::getDb()->exec("ALTER TABLE ticket_snippets ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL");
			App::getDb()->exec("ALTER TABLE ticket_snippets ADD FOREIGN KEY (category_id) REFERENCES ticket_snippet_categories(id) ON DELETE SET NULL");
			App::getDb()->exec("ALTER TABLE ticket_snippet_categories ADD FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE SET NULL");
			App::getDb()->exec("ALTER TABLE ticket_snippetcat_to_team ADD FOREIGN KEY (team_id) REFERENCES agent_teams(id) ON DELETE CASCADE");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		$this->output->writeln('Add sample categories');

		$c = array(
			'person_id' => 20001,
			'is_global' => 1,
			'title' => 'Misc'
		);
		App::getDb()->insert('ticket_snippet_categories', $c);

		$c = array(
			'person_id' => 20001,
			'is_global' => 1,
			'title' => 'Licensing'
		);
		App::getDb()->insert('ticket_snippet_categories', $c);

		$c = array(
			'person_id' => 20001,
			'is_global' => 1,
			'title' => 'Bug Report'
		);
		App::getDb()->insert('ticket_snippet_categories', $c);

		$this->output->writeln('Add sample snippets');

		try {
			$s = array(
				'person_id' => 20001,
				'category_id' => 1,
				'title' => 'Feature Suggestion',
				'snippet' => "Dear {{ person.display_name }},\r\n\r\nThank you for the feature suggestion. We now use DeskPRO itself to track feature suggestions using the DeskPRO plugin built for this task. Please submit your feature suggestion to http://helpdesk.deskpro.com/ideas.php if it has not already been suggested, or vote on the feature if it has.\r\n\r\nIf your need is urgent; DeskPRO staff are available for DeskPRO based custom development work and we would be happy to discuss your needs further."
			);
			App::getDb()->insert('ticket_snippets', $s);

			$s = array(
				'person_id' => 20001,
				'category_id' => 3,
				'title' => 'Bug Report',
				'snippet' => 'Thank you for your bug report. I\'ve create a new issue in our tracker here: http://forums.deskpro.com/project.php?issueid=<ISSUE-ID>

You can subscribe to the issue using the "Issue Tools" drop-down menu near the top right of the description. A developer will take a look and if a patch is possible, he will attach a fix that you can download.'
			);
			App::getDb()->insert('ticket_snippets', $s);

			$s = array(
				'person_id' => 20001,
				'category_id' => 2,
				'title' => 'Unlicensed - Add supported email',
				'snippet' => 'Hi there,

To receive support through this helpdesk you must have an active license and your email address must be entered under the "Support" tab of your members area. Up to three email addresses can be added.

You can log in to your members area here: http://members.deskpro.com/

After completing this simple procedure, we\'ll be able to answer whatever questions you may have.'
			);
			App::getDb()->insert('ticket_snippets', $s);

			$s = array(
				'person_id' => 20001,
				'category_id' => 2,
				'title' => 'Friendly Support Expired',
				'snippet' => 'Hi,

I hope the previous reply helped resolve this question for you. Please note that your license is currently expired; and thus you are not eligible for support and upgrades to the DeskPRO software. To receive further support you will need to renew your license from the members area at http://members.deskpro.com/ - if you have any questions about this please don\'t hesitate to ask.'
			);
			App::getDb()->insert('ticket_snippets', $s);

			$s = array(
				'person_id' => 20001,
				'category_id' => 2,
				'title' => 'Wrong Site',
				'snippet' => 'Thank you for contacting DeskPRO support.

Unfortunately you have contacted the wrong company. We provide a software application that other sites use to manage their customer support. We have no knowledge of the issue you are asking about and you will need to take this up with the company in question.

If this is a mistake and you are interested in purchasing a DeskPRO license for your website, please do reply back.'
			);
			App::getDb()->insert('ticket_snippets', $s);
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
