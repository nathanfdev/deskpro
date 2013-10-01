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

class DatabaseTestCase extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		parent::setUp();

		$db = DpTestConfig::getContainer()->getDb();
		$db->exec("SET FOREIGN_KEY_CHECKS = 0");

		#------------------------------
		# Clear database
		#------------------------------

		printf("Clearing database ...\n");
		$t_start = microtime(true);

		foreach ($db->fetchAllCol("SHOW TABLES") as $t) {
			$db->exec("DROP TABLE $t");
			echo ".";
		}
		printf("\nDone in %.4fs", microtime(true)-$t_start);


		#------------------------------
		# Create tables
		#------------------------------

		printf("Creating tables ...\n");
		$t_start = microtime(true);

		$gs = new \Application\InstallBundle\Data\GenerateSchema(DpTestConfig::getContainer()->getEm());

		printf("Creates -- ");
		foreach ($gs->getCreates() as $q) {
			$db->exec($q);
		}
		printf("Done\n");

		printf("Alters -- ");
		foreach ($gs->getAlters() as $q) {
			$db->exec($q);
		}
		printf("Done\n");
		printf("\nDone in %.4fs", microtime(true)-$t_start);


		$db->exec("SET FOREIGN_KEY_CHECKS = 1");
	}

	public function tearDown()
	{
		DpTestConfig::resetContainer();
	}

	/**
	 * @return \Application\DeskPRO\DBAL\Connection
	 */
	protected function getDb()
	{
		return DpTestConfig::getContainer()->getDb();
	}
}