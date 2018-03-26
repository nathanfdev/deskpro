<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\App\Package;

class Manifest
{
    /**
     * @var string
     */
    private $package_name;

    /**
     * @var bool
     */
    private $is_native = false;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var int
     */
    private $api_version;

    /**
     * @var int
     */
    private $version;

    /**
     * @var string
     */
    private $version_name;

    /**
     * @var bool
     */
    private $is_single = false;

    /**
     * @var string
     */
    private $author_name;

    /**
     * @var string
     */
    private $author_email;

    /**
     * @var string
     */
    private $author_link;

    /**
     * @var array
     */
    private $tags = [];

    /**
     * @var array
     */
    private $trigger_events = [];

    /**
     * @var array
     */
    private $settings_def = [];

    /**
     * @param string $package_name
     */
    public function setPackageName($package_name)
    {
        $this->package_name = $package_name;
    }

    /**
     * @return string
     */
    public function getPackageName()
    {
        return $this->package_name;
    }

    /**
     * @return bool
     */
    public function getIsNative()
    {
        return $this->is_native;
    }

    /**
     * @param bool $is_native
     */
    public function setIsNative($is_native)
    {
        $this->is_native = (bool) $is_native;
    }

    /**
     * @param int $api_version
     */
    public function setApiVersion($api_version)
    {
        $this->api_version = (int) $api_version;
    }

    /**
     * @return int
     */
    public function getApiVersion()
    {
        return $this->api_version;
    }

    /**
     * @param string $author_email
     */
    public function setAuthorEmail($author_email)
    {
        $this->author_email = $author_email;
    }

    /**
     * @return string
     */
    public function getAuthorEmail()
    {
        return $this->author_email;
    }

    /**
     * @param string $author_link
     */
    public function setAuthorLink($author_link)
    {
        $this->author_link = $author_link;
    }

    /**
     * @return string
     */
    public function getAuthorLink()
    {
        return $this->author_link;
    }

    /**
     * @param string $author_name
     */
    public function setAuthorName($author_name)
    {
        $this->author_name = $author_name;
    }

    /**
     * @return string
     */
    public function getAuthorName()
    {
        return $this->author_name;
    }

    /**
     * @param bool $is_single
     */
    public function setIsSingle($is_single)
    {
        $this->is_single = $is_single;
    }

    /**
     * @return bool
     */
    public function getIsSingle()
    {
        return $this->is_single;
    }

    /**
     * @return array
     */
    public function getTriggerEvents()
    {
        return $this->trigger_events;
    }

    /**
     * @param array $trigger_events
     */
    public function setTriggerEvents(array $trigger_events)
    {
        $this->trigger_events = $trigger_events;
    }

    /**
     * @param array $settings_def
     */
    public function setSettingsDef($settings_def)
    {
        $this->settings_def = $settings_def;
    }

    /**
     * @return array
     */
    public function getSettingsDef()
    {
        return $this->settings_def;
    }

    /**
     * @param array $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param int $version
     */
    public function setVersion($version)
    {
        $this->version = (int) $version;
        if (!$this->version_name) {
            $this->version_name = "v$version";
        }
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version_name
     */
    public function setVersionName($version_name)
    {
        $this->version_name = $version_name;
    }

    /**
     * @return string
     */
    public function getVersionName()
    {
        return $this->version_name;
    }
}
