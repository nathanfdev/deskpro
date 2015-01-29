<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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
 * @subpackage
 */

namespace Application\InstallBundle\Upgrade\Build;

class Build1422548532 extends AbstractBuild
{
    public function run()
    {
        $this->out("new brand settings table");
		$this->execMutateSql("CREATE TABLE settings_brand (id INT AUTO_INCREMENT NOT NULL, brand_id INT NOT NULL, name VARCHAR(255) NOT NULL, value BLOB DEFAULT NULL, INDEX IDX_A48BBF1144F5D008 (brand_id), UNIQUE INDEX unique_settings_per_brand (name, brand_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->execMutateSql("ALTER TABLE settings_brand ADD CONSTRAINT FK_A48BBF1144F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE CASCADE");
		$this->execMutateSql("ALTER TABLE settings DROP FOREIGN KEY FK_E545A0C544F5D008");
		$this->execMutateSql("DROP INDEX unique_settings_per_brand ON settings");
		$this->execMutateSql("DROP INDEX IDX_E545A0C544F5D008 ON settings");
		$this->execMutateSql("ALTER TABLE settings DROP brand_id");
		$this->execMutateSql("CREATE UNIQUE INDEX unique_setting_name ON settings (name)");
    }
}