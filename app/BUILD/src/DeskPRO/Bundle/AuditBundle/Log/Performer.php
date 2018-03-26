<?php

namespace DeskPRO\Bundle\AuditBundle\Log;

/**
 * Class Performer.
 */
class Performer
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $id;

    /**
     * Performer constructor.
     *
     * @param string $name
     * @param int    $id
     */
    public function __construct($name, $id = null)
    {
        $this->name = $name;
        $this->id   = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }
}
