<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
     * @var boolean
     */
    private $isBackendOnly;

    /**
     * @JMS\Type("boolean")
     * @JMS\SerializedName("required")
     *
     * @var boolean
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
    public function setRequired( $isRequired)
    {
        $this->isBackendOnly = $isRequired;
    }
}
