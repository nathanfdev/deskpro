<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\LegacyApiBundle\PermissionStrategy;

use Application\DeskPRO\Entity\ApiKey;
use Application\LegacyApiBundle\ApiUser;

/**
 * The AdminManage permission is applied to APIs that:
 * - Have an admin user logged in (eg they have a session tied to their key)
 * - Their API key has a 'admin_manage' flag.
 */
class AdminManagePermission implements PermissionStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function userHasPermission(ApiUser $api_user, $context_info = null)
    {
        // The API key itself has the correct flag set
        if ($api_user->api_key) {
            if ($api_user->api_key->isFlagSet(ApiKey::FLAG_ADMIN_MANAGE)) {
                return true;
            }
        }

        // This is a logged-in session
        if ($api_user->session && $api_user->person && $api_user->person->can_admin) {
            return true;
        }

        return false;
    }
}
