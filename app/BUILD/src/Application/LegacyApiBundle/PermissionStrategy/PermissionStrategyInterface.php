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
     * @param ApiUser $api_user
     * @param mixed   $context_info
     *
     * @return bool
     */
    public function userHasPermission(ApiUser $api_user, $context_info = null);
}
