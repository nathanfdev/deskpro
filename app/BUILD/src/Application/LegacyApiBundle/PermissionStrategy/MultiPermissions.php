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
    public function userHasPermission(ApiUser $api_user, $context_info = null)
    {
        if (empty($this->perms)) {
            return true;
        }

        if ($this->fn) {
            $type = $this->fn($context_info);
        } else {
            if (!empty($context_info['type'])) {
                $type = $context_info['type'];
            } else {
                $type = 'default';
            }
        }

        $check_perms = null;

        if ($type == 'default') {
            if (isset($this->perms['default'])) {
                $check_perms = $this->perms['default'];
            }
        } else {
            if (isset($this->perms[$type])) {
                $check_perms = $this->perms[$type];
            } elseif (isset($this->perms['default'])) {
                $check_perms = $this->perms['default'];
            }
        }

        if (!$check_perms) {
            return true;
        }

        foreach ($check_perms as $p) {
            if (!$p->userHasPermission($api_user, $context_info)) {
                return false;
            }
        }

        return true;
    }
}
