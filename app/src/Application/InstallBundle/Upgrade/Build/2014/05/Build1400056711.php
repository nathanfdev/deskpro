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

class Build1400056711 extends AbstractBuild
{
    public function run()
    {
        $db = $this->container->getDb();

        $this->out("New email_accounts table");
        $db->exec("CREATE TABLE email_accounts (id INT AUTO_INCREMENT NOT NULL, account_type VARCHAR(255) NOT NULL, incoming_account LONGTEXT DEFAULT NULL COMMENT '(DC2Type:dp_json_obj)', outgoing_account LONGTEXT DEFAULT NULL COMMENT '(DC2Type:dp_json_obj)', is_enabled TINYINT(1) NOT NULL, address VARCHAR(255) NOT NULL, other_addresses LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)', options LONGTEXT DEFAULT NULL COMMENT '(DC2Type:json_array)', date_created DATETIME NOT NULL, date_read_start DATETIME DEFAULT NULL, date_last_incoming DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");

        $this->out("Drop field departments.email_gateway_id");
        try { $db->exec("ALTER TABLE departments DROP INDEX IDX_16AEB8D4FBCC7CDF"); } catch (\Exception $e) {}
        try { $db->exec("ALTER TABLE departments DROP INDEX FK_16AEB8D4FBCC7CDF"); } catch (\Exception $e) {}
        $db->exec("ALTER TABLE departments DROP FOREIGN KEY FK_16AEB8D4FBCC7CDF");
        $db->exec("ALTER TABLE departments DROP email_gateway_id");
    }
}
