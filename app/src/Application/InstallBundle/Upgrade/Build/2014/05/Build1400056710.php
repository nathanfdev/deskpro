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

class Build1400056710 extends AbstractBuild
{
    public function run()
    {
        $db = $this->container->getDb();

        $this->out("Alter usersources table");
        $db->exec("ALTER TABLE usersources DROP FOREIGN KEY FK_4E3C994CEB0D3362, DROP INDEX IDX_4E3C994CEB0D3362");
        $db->exec("ALTER TABLE usersources DROP usersource_plugin_id");
        $db->exec("ALTER TABLE usersources ADD app_id INT DEFAULT NULL");
        $db->exec("ALTER TABLE usersources ADD CONSTRAINT FK_4E3C994C7987212D FOREIGN KEY (app_id) REFERENCES app_instances (id) ON DELETE CASCADE");
        $db->exec("CREATE INDEX IDX_4E3C994C7987212D ON usersources (app_id)");

        $this->out("Drop old usersource_plugins table");
        $db->exec("DROP TABLE IF EXISTS usersource_plugins");
    }
}
