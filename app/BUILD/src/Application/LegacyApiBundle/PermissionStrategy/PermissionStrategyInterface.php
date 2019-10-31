<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\LegacyApiBundle\PermissionStrategy;

use Application\LegacyApiBundle\ApiUser;

interface PermissionStrategyInterface
{
    /**
     * @param ApiUser $apiUser
     * @param mixed   $contextInfo
     *
     * @return bool
     */
    public function userHasPermission(ApiUser $apiUser, $contextInfo = null);
}
