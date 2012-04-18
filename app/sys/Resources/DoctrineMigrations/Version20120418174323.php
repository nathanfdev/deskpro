<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20120418174323 extends AbstractMigration
{
	public function up(Schema $schema)
	{
		$this->addSql("TRUNCATE TABLE portal_page_display");
        $this->addSql("
        	INSERT INTO `portal_page_display` (`type`, `is_enabled`, `section`, `data`)
			VALUES
				('news', 1, 'portal', 'a:0:{}'),
				('staff', 1, 'sidebar', 'a:0:{}'),
				('kb_cat_list', 1, 'sidebar', 'a:0:{}'),
				('feedback_cat_list', 1, 'sidebar', 'a:0:{}'),
				('userinfo', 1, 'sidebar', 'a:0:{}')
        ");
	}

	public function down(Schema $schema)
	{
		throw new \BadMethodCallException("down() is not supported.");
	}
}
