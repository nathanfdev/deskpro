<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

class Build1400056715 extends AbstractBuild
{
    public function run()
    {
        $db = $this->container->getDb();

        $this->out("Alter tickets table to remove old fields");
        $this->execMutateSql("ALTER TABLE tickets DROP FOREIGN KEY FK_54469DF4FBCC7CDF, DROP FOREIGN KEY FK_54469DF4F2598614", true);
        $this->execMutateSql("ALTER TABLE tickets DROP KEY IDX_54469DF4FBCC7CDF, DROP KEY IDX_54469DF4F2598614", true);
        $db->exec("ALTER TABLE tickets DROP email_gateway_id, DROP email_gateway_address_id, DROP notify_email, DROP notify_email_name, DROP notify_email_agent, DROP notify_email_name_agent");
    }
}
