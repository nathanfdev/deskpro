<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110420052537 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Add content relations');

		try {
			App::getDb()->exec("DROP TABLE IF EXISTS related_content");
			App::getDb()->exec("CREATE TABLE related_content (object_type VARCHAR(100) NOT NULL, object_id INT NOT NULL, rel_object_type VARCHAR(100) NOT NULL, rel_object_id INT NOT NULL, PRIMARY KEY(object_type, object_id, rel_object_type, rel_object_id)) ENGINE = InnoDB");

			$types = array('articles', 'downloads', 'ideas', 'news');
			foreach ($types as $t) {
				App::getDb()->insert('related_content', array(
					'object_type' => 'articles',
					'object_id' => '3',
					'rel_object_type' => $t,
					'rel_object_id' => '1'
				));
			}
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
