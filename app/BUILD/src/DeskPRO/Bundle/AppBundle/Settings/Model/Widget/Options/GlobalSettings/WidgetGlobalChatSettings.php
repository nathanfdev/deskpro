<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\GlobalSettings;

use JMS\Serializer\Annotation as JMS;

/**
 * Class WidgetGlobalChatSettings.
 */
class WidgetGlobalChatSettings
{
    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $enabled = true;

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
}
