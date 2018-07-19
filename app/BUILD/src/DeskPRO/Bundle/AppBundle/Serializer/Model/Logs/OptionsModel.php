<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Logs;

use JMS\Serializer\Annotation as JMS;

/**
 * Class OptionsModel.
 */
class OptionsModel
{
    /**
     * Is log enabled.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $enabled;

    /**
     * Maximum request body size (in B).
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $requestLength;

    /**
     * Maximum response body size (in B).
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $responseLength;

    /**
     * Which modes to log (if enabled).
     *
     * @JMS\Type("array<string>")
     *
     * @var array
     */
    protected $modes = [];

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     *
     * @return $this
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return int
     */
    public function getRequestLength()
    {
        return $this->requestLength;
    }

    /**
     * @param int $requestLength
     *
     * @return $this
     */
    public function setRequestLength($requestLength)
    {
        $this->requestLength = $requestLength;

        return $this;
    }

    /**
     * @return int
     */
    public function getResponseLength()
    {
        return $this->responseLength;
    }

    /**
     * @param int $responseLength
     *
     * @return $this
     */
    public function setResponseLength($responseLength)
    {
        $this->responseLength = $responseLength;

        return $this;
    }

    /**
     * @return array
     */
    public function getModes()
    {
        return $this->modes;
    }

    /**
     * @param array $modes
     *
     * @return $this
     */
    public function setModes(array $modes)
    {
        $this->modes = $modes;

        return $this;
    }
}
