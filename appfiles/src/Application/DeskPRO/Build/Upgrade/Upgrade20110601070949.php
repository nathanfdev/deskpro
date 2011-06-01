<?php

namespace Application\DeskPRO\Build\Upgrade;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Build\Upgrader;

class Upgrade20110601070949 extends UpgradeAbstract
{
	public function step1()
	{
		$this->output->writeln('Some attachment stuff');

		try {
			App::getDb()->exec("
				ALTER TABLE  `blobs`
				ADD  `dim_w` INT NOT NULL DEFAULT  '0' AFTER  `authcode` ,
				ADD  `dim_h` INT NOT NULL DEFAULT  '0' AFTER  `dim_w`
			");
			App::getDb()->exec("
				CREATE TABLE labels_blobs (
					blob_id INT NOT NULL,
					label VARCHAR(255) NOT NULL,
					INDEX IDX_EC63B2F0ED3E8EA5 (blob_id),
					PRIMARY KEY(blob_id, label)
				) ENGINE = InnoDB
			");
			App::getDb()->exec("
				CREATE TABLE blob_object_attach (
					object_type VARCHAR(100) NOT NULL,
					blob_id INT DEFAULT NULL,
					object_id INT NOT NULL,
					INDEX IDX_D048A866ED3E8EA5 (blob_id),
					PRIMARY KEY(object_type)
				) ENGINE = InnoDB
			");
		} catch (\Exception $e) {
			$this->output->writeln("ERROR: {$e->getMessage()}");
			return Upgrader::STEP_FAILED;
		}

		return Upgrader::STEP_DONE;
	}
}
