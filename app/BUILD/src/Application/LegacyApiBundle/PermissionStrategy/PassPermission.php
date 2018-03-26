<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\LegacyApiBundle\PermissionStrategy;

use Application\LegacyApiBundle\ApiUser;

class PassPermission implements PermissionStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function userHasPermission(ApiUser $api_user, $context_info = null)
    {
        if ($api_user->api_key || $api_user->api_token) {
            return true;
        }

        return false;
    }
}
