<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain\AppManifest;

use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\Discriminator(field = "type", map = {
 *    "text": "DeskPRO\Bundle\AppStoreBundle\Domain\AppManifest\SettingText",
 *    "textarea": "DeskPRO\Bundle\AppStoreBundle\Domain\AppManifest\SettingTextarea",
 *    "choice": "DeskPRO\Bundle\AppStoreBundle\Domain\AppManifest\SettingChoice",
 *    "boolean": "DeskPRO\Bundle\AppStoreBundle\Domain\AppManifest\SettingBoolean"
 * })
 */
abstract class Setting
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $name;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $title;

    /**
     * @JMS\Type("boolean")
     * @JMS\SerializedName("isBackendOnly")
     *
     * @var bool
     */
    private $isBackendOnly;

    /**
     * @JMS\Type("boolean")
     * @JMS\SerializedName("required")
     *
     * @var bool
     */
    private $isRequired;

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

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return bool
     */
    public function isBackendOnly()
    {
        return $this->isBackendOnly;
    }

    /**
     * @param bool $isBackendOnly
     */
    public function setIsBackendOnly($isBackendOnly)
    {
        $this->isBackendOnly = $isBackendOnly;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->isRequired;
    }

    /**
     * @param bool $isRequired
     */
    public function setRequired($isRequired)
    {
        $this->isBackendOnly = $isRequired;
    }
}
