<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\Core\Attachments;

use JMS\Serializer\Annotation as JMS;

/**
 * Class AttachmentSettings.
 */
class AttachmentSettings
{
    /**
     * @var string
     *
     * @JMS\Type("integer")
     */
    private $maxSize;

    /**
     * @var array
     *
     * @JMS\Type("array<string>")
     */
    private $whitelist = [];

    /**
     * @var array
     *
     * @JMS\Type("array<string>")
     */
    private $blacklist = [];

    /**
     * @return string
     */
    public function getMaxSize()
    {
        return $this->maxSize;
    }

    /**
     * @param string $maxSize
     *
     * @return $this
     */
    public function setMaxSize($maxSize)
    {
        $this->maxSize = $maxSize;

        return $this;
    }

    /**
     * @return array
     */
    public function getWhitelist()
    {
        return $this->whitelist;
    }

    /**
     * @param array $whitelist
     *
     * @return $this
     */
    public function setWhitelist(array $whitelist)
    {
        $this->whitelist = $whitelist;

        return $this;
    }

    /**
     * @return array
     */
    public function getBlacklist()
    {
        return $this->blacklist;
    }

    /**
     * @param array $blacklist
     *
     * @return $this
     */
    public function setBlacklist(array $blacklist)
    {
        $this->blacklist = $blacklist;

        return $this;
    }
}
