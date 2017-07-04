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

namespace DeskPRO\Bundle\AppStoreBundle\Domain;

use JMS\Serializer\Annotation as JMS;

/**
 * Representation of an application's manifest. This is a mutable class to be used when creating other objects, like Application.
 */
class AppManifest
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
    private $version;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $title;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $description;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $scope;

    /**
     * @JMS\Type("array<DeskPRO\Bundle\AppStoreBundle\Domain\AppManifestSetting>")
     *
     * @var AppManifestSetting[]
     */
    private $settings = [];

    /**
     * @JMS\Type("array<DeskPRO\Bundle\AppStoreBundle\Domain\AppManifestTarget>")
     * @var AppManifestTarget[]
     */
    private $targets = [];

    /**
     * @JMS\Type("array")
     * @JMS\SerializedName("defaultSettings")
     *
     * @var array
     */
    private $defaultSettings;

    /**
     * @JMS\Type("DeskPRO\Bundle\AppStoreBundle\Domain\AppManifestAuthor")
     *
     * @var AppManifestAuthor
     */
    private $author;

    /**
     * @JMS\Type("array<string>")
     * @JMS\SerializedName("externalApis")
     *
     * @var string[]
     */
    private $externalApis = [];

    /**
     * @JMS\Type("array<string>")
     * @JMS\SerializedName("$deskproApiTags")
     *
     * @var string[]
     */
    private $deskproApiTags = [];

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $isSingle = false;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version
     *
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
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
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param string $scope
     *
     * @return $this
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * @return array
     */
    public function getDefaultSettings()
    {
        return $this->defaultSettings;
    }

    /**
     * @param array $defaultSettings
     *
     * @return $this
     */
    public function setDefaultSettings(array $defaultSettings)
    {
        $this->defaultSettings = $defaultSettings;

        return $this;
    }

    /**
     * @return AppManifestAuthor
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param AppManifestAuthor $author
     *
     * @return $this
     */
    public function setAuthor(AppManifestAuthor $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return AppManifestSetting[]
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param AppManifestSetting[] $settings
     *
     * @return $this
     */
    public function setSettings(array $settings)
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * @return \string[]
     */
    public function getExternalApis()
    {
        return $this->externalApis;
    }

    /**
     * @param string[] $externalApis
     *
     * @return $this
     */
    public function setExternalApis(array $externalApis)
    {
        $this->externalApis = $externalApis;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSingle()
    {
        return $this->isSingle;
    }

    /**
     * @param bool $isSingle
     *
     * @return $this
     */
    public function setIsSingle($isSingle)
    {
        $this->isSingle = $isSingle;

        return $this;
    }

    /**
     * @return AppManifestTarget[]
     */
    public function getTargets()
    {
        return $this->targets;
    }

    /**
     * @param AppManifestTarget[] $targets
     *
     * @return $this
     */
    public function setTargets(array $targets)
    {
        $this->targets = $targets;

        return $this;
    }

    /**
     * @return \string[]
     */
    public function getDeskproApiTags()
    {
        return $this->deskproApiTags;
    }

    /**
     * @param \string[] $deskproApiTags
     */
    public function setDeskproApiTags(array $deskproApiTags)
    {
        $this->deskproApiTags = $deskproApiTags;
    }
}
