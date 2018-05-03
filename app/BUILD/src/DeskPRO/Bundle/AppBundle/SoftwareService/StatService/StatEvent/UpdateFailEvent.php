<?php

namespace DeskPRO\Bundle\AppBundle\SoftwareService\StatService\StatEvent;

class UpdateFailEvent
{
    /**
     * @var string
     */
    private $uuid;

    /**
     * @var string
     */
    private $summary;

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
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @param string $summary
     *
     * @return $this
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;

        return $this;
    }
}
