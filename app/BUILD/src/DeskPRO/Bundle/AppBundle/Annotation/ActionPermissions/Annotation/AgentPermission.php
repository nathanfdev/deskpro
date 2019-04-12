<?php

namespace DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation;

/**
 * @Annotation
 * Class AgentPermission
 */
class AgentPermission
{
    /**
     * @var string
     */
    private $name;

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = isset($name['value']) ? $name['value'] : null;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
