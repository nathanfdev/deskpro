<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Person\Context;

class CreatePersonContext
{
    protected $creation_system;
    protected $name;

    public function __construct($creation_system)
    {
        $this->creation_system = $creation_system;
    }

    /**
     * @return mixed
     */
    public function getCreationSystem()
    {
        return $this->creation_system;
    }

    /**
     * @param mixed $creation_system
     */
    public function setCreationSystem($creation_system)
    {
        $this->creation_system = $creation_system;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}
