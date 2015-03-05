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

use Application\InstallBundle\Upgrade\Build\Helper201405\UsersourceUpgrader;

class Build1400056731 extends AbstractBuild
{
    public function run()
    {
        require_once DP_ROOT.'/src/Application/InstallBundle/Upgrade/Build/2014/05/Helper/UsersourceUpgrader.php';

        $db = $this->container->getDb();
        $this->out("Upgrade usersources");

        $usersources = $db->fetchAll("SELECT * FROM usersources");
        foreach ($usersources as $us) {
            $this->out("Upgrading {$us['id']} -- {$us['source_type']}");
            $up = new UsersourceUpgrader($this->container, $us);
            $up->upgrade();
        }
    }
}
