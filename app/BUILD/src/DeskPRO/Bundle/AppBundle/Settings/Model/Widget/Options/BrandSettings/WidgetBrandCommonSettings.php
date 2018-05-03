<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class WidgetBrandCommonSettings.
 */
class WidgetBrandCommonSettings
{
    const TYPE_COLUMN = 'column';
    const TYPE_BUBBLE = 'bubble';

    const POSITION_LEFT  = 'left';
    const POSITION_RIGHT = 'right';

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @Assert\NotBlank()
     */
    private $type = self::TYPE_COLUMN;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @Assert\NotBlank()
     */
    private $position = self::POSITION_RIGHT;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     * @Assert\GreaterThanOrEqual(300)
     */
    private $agentPollingTimeout;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $enabled = true;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $primaryColor = '#62ad8c';

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param string $position
     *
     * @return $this
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return int
     */
    public function getAgentPollingTimeout()
    {
        return $this->agentPollingTimeout;
    }

    /**
     * @param int $agentPollingTimeout
     *
     * @return $this
     */
    public function setAgentPollingTimeout($agentPollingTimeout)
    {
        $this->agentPollingTimeout = $agentPollingTimeout;

        return $this;
    }

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
        $this->enabled = (bool) $enabled;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrimaryColor()
    {
        return $this->primaryColor;
    }

    /**
     * @param string $primaryColor
     */
    public function setPrimaryColor($primaryColor)
    {
        $this->primaryColor = $primaryColor;
    }
}
