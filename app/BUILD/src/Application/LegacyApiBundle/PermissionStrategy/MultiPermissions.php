<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\LegacyApiBundle\PermissionStrategy;

use Application\LegacyApiBundle\ApiUser;

/**
 * Checks a PermissionStrategyInterface depending on a 'type' (i.e., an action name).
 * Lets you specificy specific permissions based on the request, rather than a controller-wide permission scheme.
 */
class MultiPermissions implements PermissionStrategyInterface
{
    /**
     * Array of type => array(PermissionStrategyInterface).
     *
     * @var array
     */
    private $perms = [];

    /**
     * @var Callback
     */
    private $fn = null;

    /**
     * @param null|Callback $fn Optionally a callback that returns the type name to apply for a check
     */
    public function __construct($fn = null)
    {
        $this->fn = $fn;
    }

    /**
     * @param PermissionStrategyInterface $p
     * @param string                      $type
     */
    public function addPermissionStrategy(PermissionStrategyInterface $p, $type = 'default')
    {
        if (!isset($this->perms[$type])) {
            $this->perms[$type] = [];
        }
        $this->perms[$type][] = $p;
    }

    /**
     * {@inheritdoc}
     */
    public function userHasPermission(ApiUser $apiUser, $contextInfo = null)
    {
        $checkPerms = $this->perms;
        if (empty($checkPerms['default'])) {
            // no permissions are set, use the 'agent' one as fallback
            $checkPerms['default'][] = new AgentPermission();
        }

        if ($this->fn) {
            $type = $this->fn($contextInfo);
        } else {
            if (!empty($contextInfo['type'])) {
                $type = $contextInfo['type'];
            } else {
                $type = 'default';
            }
        }

        $check_perms = null;

        if ($type == 'default') {
            if (isset($checkPerms['default'])) {
                $check_perms = $checkPerms['default'];
            }
        } else {
            if (isset($checkPerms[$type])) {
                $check_perms = $checkPerms[$type];
            } elseif (isset($checkPerms['default'])) {
                $check_perms = $checkPerms['default'];
            }
        }

        if (!$check_perms) {
            return true;
        }

        foreach ($check_perms as $p) {
            if (!$p->userHasPermission($apiUser, $contextInfo)) {
                return false;
            }
        }

        return true;
    }
}
