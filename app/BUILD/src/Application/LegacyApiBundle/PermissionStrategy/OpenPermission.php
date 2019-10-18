<?php

namespace Application\LegacyApiBundle\PermissionStrategy;

use Application\LegacyApiBundle\ApiUser;

/**
 * Class OpenPermission.
 */
class OpenPermission implements PermissionStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function userHasPermission(ApiUser $apiUser, $contextInfo = null)
    {
        // always allowed for everyone
        return true;
    }
}
