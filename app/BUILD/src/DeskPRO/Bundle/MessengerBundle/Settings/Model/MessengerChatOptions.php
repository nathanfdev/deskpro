<?php

namespace DeskPRO\Bundle\MessengerBundle\Settings\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * Class MessengerChatOptions.
 */
class MessengerChatOptions
{
    /**
     * Indicates whenever agent avatar should be shown or not.
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("showAgentPhotos")
     *
     * @var bool
     */
    private $showAgentPhotos = false;

    /**
     * @return bool
     */
    public function isShowAgentPhotos()
    {
        return $this->showAgentPhotos;
    }

    /**
     * @param bool $showAgentPhotos
     *
     * @return $this
     */
    public function setShowAgentPhotos($showAgentPhotos)
    {
        $this->showAgentPhotos = $showAgentPhotos;

        return $this;
    }
}
