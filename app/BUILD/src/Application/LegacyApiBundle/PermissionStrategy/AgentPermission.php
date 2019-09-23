<?php

namespace Application\LegacyApiBundle\PermissionStrategy;

/**
 * Class AgentPermission.
 */
class AgentPermission extends UserTypePermission
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(self::AGENT);
    }
}
