<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\LegacyApiBundle\Controller;

/**
 * Interface ProtectedControllerInterface.
 */
interface ProtectedControllerInterface
{
    /**
     * @return \Application\LegacyApiBundle\PermissionStrategy\PermissionStrategyInterface
     */
    public function getPermissionStrategy();
}
