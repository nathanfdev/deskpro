<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\LegacyApiBundle\PermissionStrategy;

use Application\LegacyApiBundle\ApiUser;

/**
 * Strategy that says we require an active session.
 */
class RequireSessionPermission implements PermissionStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function userHasPermission(ApiUser $api_user, $context_info = null)
    {
        return $api_user->session ? true : false;
    }
}
