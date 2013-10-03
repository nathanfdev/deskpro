<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

use Behat\Behat\Context\BehatContext;

class DeskproContext extends \Behat\MinkExtension\Context\MinkContext
{
	private $has_reset_db = false;

	/**
	 * Resets the database
	 */
	protected function initTestDb()
	{
		if ($this->has_reset_db) return;
		$this->has_reset_db = true;

		DpTestConfig::initTestDb();
	}


	/**
	 * @return \Application\DeskPRO\DBAL\Connection
	 */
	private function getDb()
	{
		return DpTestConfig::getContainer()->getDb();
	}


	/**
	 * @Given /^I have a user "([^"]*)" with password "([^"]*)"$/
	 */
	public function iHaveAUserWithPassword($email, $password)
	{
		$this->initTestDb();
		$person_id = $this->getDb()->fetchColumn("SELECT person_id FROM people_emails WHERE email = ?", array($email));

		if (!$person_id) {
			$person_id = $this->getDb()->insert('people', array(
				'is_contact'         => 1,
				'is_user'            => 1,
				'is_confirmed'       => 1,
				'is_agent_confirmed' => 1,
				'creation_system'    => 'web.person',
				'name'               => 'Test User',
				'first_name'         => 'Test',
				'last_name'          => 'User',
				'secret_string'      => 'kGuGXxNvCrEwOQ80U2z8Xqqvhok2pYqZIRnUuQOn',
				'timezone'           => 'Europe/London',
				'salt'               => 'RTtaBuOTrJFvX12JkeoJw4RPgEuhXTOBHrpGzULj',
				'date_created'       => '2013-10-03 05:05:05',
				'date_last_login'    => '2013-10-03 05:05:05'
			));

			$email_id = $this->getDb()->insert('people_emails', array(
				'person_id'      => $person_id,
				'email'          => $email,
				'is_validated'   => 1,
				'date_created'   => '2013-10-03 05:05:05',
				'date_validated' => '2013-10-03 05:05:05'
			));

			$this->getDb()->update('people', array('primary_email_id' => $email_id), array('id' => $person_id));
		}

		// Set password
		$hasher = new \Application\DeskPRO\People\PasswordScheme\Bcrypt();
		$this->getDb()->update('people', array(
			'password_scheme' => 'bcrypt',
			'password'        => $hasher->hashPassword(new \Application\DeskPRO\Entity\Person(), $password)
		), array('id' => $person_id));

		return true;
	}
}