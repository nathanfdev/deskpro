<?php

namespace DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation;

/**
 * Class RequireAgentPermissions.
 *
 * @Annotation
 */
class RequireAgentPermissions
{
    /**
     * @var bool
     */
    public $excludeAdmin = false;
}
