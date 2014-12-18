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
 * @category People
 */

namespace Application\DeskPRO\People\AgentPermissions;

use Application\DeskPRO\Entity\Usergroup;
use Application\InstallBundle\Data\AgentGroupPermScanner;

class PermissionNamesLoader
{
    /**
     * @var bool
     */
    private $debug = false;

    /**
     * @var array
     */
    private $all_names = null;

    /**
     * @var array
     */
    private $all_safe_names = null;


    private function load()
    {
        $cache_path = DP_ROOT . '/sys/Resources/agent-perm-names.php';
        if ($this->debug || !file_exists($cache_path)) {
            $scanner = new AgentGroupPermScanner();
            $perm_names = array(
                'all' => $scanner->getNames(),
                'safe' => $scanner->getSafeNames()
            );
        } else {
            $perm_names = require($cache_path);
        }

        $this->all_names = $perm_names['all'];
        $this->all_safe_names = $perm_names['safe'];
    }


    /**
     * @return array
     */
    public function getNames()
    {
        if ($this->all_names === null) $this->load();
        return $this->all_names;
    }


    /**
     * @return array
     */
    public function getSafeNames()
    {
        if ($this->all_safe_names === null) $this->load();
        return $this->all_safe_names;
    }


    /**
     * @param  Usergroup|string $group Usergroup or string sys_name
     * @return array
     */
    public function getEnabledForGroup($group)
    {
        if ($group instanceof $group) {
            $sys_name = $group->sys_name;
        } else {
            $sys_name = $group;
        }

        if ($sys_name == 'agent_all_perms') {
            return $this->getNames();
        } elseif ($sys_name == 'agent_all_safe_perms') {
            return $this->getNames();
        } else {
            return array();
        }
    }
}
