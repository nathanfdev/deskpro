<?php

/**
 * DeskPRO.
 *
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
        $cache_path = DP_ROOT.'/sys/Resources/agent-perm-names.php';
        if ($this->debug || !file_exists($cache_path)) {
            $scanner    = new AgentGroupPermScanner();
            $perm_names = [
                'all'  => $scanner->getNames(),
                'safe' => $scanner->getSafeNames(),
            ];
        } else {
            $perm_names = require $cache_path;
        }

        $this->all_names      = $perm_names['all'];
        $this->all_safe_names = $perm_names['safe'];
    }

    /**
     * @return array
     */
    public function getNames()
    {
        if ($this->all_names === null) {
            $this->load();
        }

        return $this->all_names;
    }

    /**
     * @return array
     */
    public function getSafeNames()
    {
        if ($this->all_safe_names === null) {
            $this->load();
        }

        return $this->all_safe_names;
    }

    /**
     * @param Usergroup|string $group Usergroup or string sys_name
     *
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
            return $this->getSafeNames();
        } else {
            return [];
        }
    }
}
