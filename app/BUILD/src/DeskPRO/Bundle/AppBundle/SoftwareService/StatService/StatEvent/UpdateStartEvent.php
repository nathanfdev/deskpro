<?php

namespace DeskPRO\Bundle\AppBundle\SoftwareService\StatService\StatEvent;

class UpdateStartEvent
{
    /**
     * @var string
     */
    private $uuid;

    /**
     * @var string
     */
    private $build;

    /**
     * @return static
     */
    public static function create()
    {
        return new static();
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     *
     * @return $this
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return string
     */
    public function getBuild()
    {
        return $this->build;
    }

    /**
     * @param string $build
     *
     * @return $this
     */
    public function setBuild($build)
    {
        $this->build = $build;

        return $this;
    }
}
