<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\LegacyApiBundle\PermissionStrategy;

use Application\LegacyApiBundle\ApiUser;

class CompositePermission implements PermissionStrategyInterface
{
    /**
     * @var PermissionStrategyInterface[]
     */
    private $perms = [];

    /**
     * @param PermissionStrategyInterface $p
     */
    public function addPermissionStrategy(PermissionStrategyInterface $p)
    {
        $this->perms[] = $p;
    }

    /**
     * {@inheritdoc}
     */
    public function userHasPermission(ApiUser $api_user, $context_info = null)
    {
        foreach ($this->perms as $p) {
            if (!$p->userHasPermission($api_user, $context_info)) {
                return false;
            }
        }

        return true;
    }
}
