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

use Orb\Util\Strings;

class Build1410519725 extends AbstractBuild
{
    public function run()
    {
        $this->out("Fix multiple sys.install.default_data records");

        $recs = $this->container->getDb()->fetchAllCol("SELECT data FROM datastore WHERE name = 'sys.install.default_data'");
        if ($recs) {
            $installed_list = array();

            foreach ($recs as $r) {
                $r = @unserialize($r);
                if (!$r || !is_array($r['installed']) || empty($r['installed'])) {
                    continue;
                }

                $installed_list = array_merge($installed_list, $r['installed']);
            }

            $installed_list = array_unique($installed_list);
            $installed_list = array_values($installed_list);

            $this->container->getDb()->delete('datastore', array('name' => 'sys.install.default_data'));
            $this->container->getDb()->insert('datastore', array(
                'name' => 'sys.install.default_data',
                'auth' => Strings::random(15),
                'data' => serialize(array('installed' => $installed_list))
            ));
        }
    }
}
