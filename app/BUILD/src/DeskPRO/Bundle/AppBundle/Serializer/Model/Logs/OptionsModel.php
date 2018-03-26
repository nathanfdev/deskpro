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
     * @var bool
     */
    protected $requestLength;

    /**
     * Maximum response body size (in B).
     *
     * @JMS\Type("integer")
     *
     * @var bool
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
     * OptionsModel constructor.
     *
     * @param bool  $enabled
     * @param int   $requestLength
     * @param int   $responseLength
     * @param array $modes
     */
    public function __construct($enabled, $requestLength, $responseLength, array $modes)
    {
        $this->enabled        = (bool) $enabled;
        $this->modes          = $modes;
        $this->requestLength  = $requestLength;
        $this->responseLength = $responseLength;
    }
}
