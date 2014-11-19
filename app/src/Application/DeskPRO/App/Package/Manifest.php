<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
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
    private $tags = array();

    /**
     * @var array
     */
    private $settings_def = array();

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
        $this->is_native = (bool)$is_native;
    }

    /**
     * @param int $api_version
     */
    public function setApiVersion($api_version)
    {
        $this->api_version = (int)$api_version;
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
     * @param boolean $is_single
     */
    public function setIsSingle($is_single)
    {
        $this->is_single = $is_single;
    }

    /**
     * @return boolean
     */
    public function getIsSingle()
    {
        return $this->is_single;
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
        $this->version = (int)$version;
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
