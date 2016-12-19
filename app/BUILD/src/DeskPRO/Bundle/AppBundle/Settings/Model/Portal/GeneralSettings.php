<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Settings\Model\Portal;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\AppBundle\Settings\Model\AbstractBrandAwareSettings;
use JMS\Serializer\Annotation as JMS;

/**
 * Class GeneralSettings.
 */
class GeneralSettings extends AbstractBrandAwareSettings
{
    /**
     * @var Brand
     *
     * @JMS\Exclude()
     */
    protected $brand;

    /**
     * Site name.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    private $siteName;

    /**
     * Site name.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    private $siteUrl;

    /**
     * HelpDesk name.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    private $deskproName;

    /**
     * HelpDesk name.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    private $deskproUrl;

    /**
     * Application feedback enabled.
     *
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $appsFeedback;

    /**
     * Application Knowledge base enabled.
     *
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $appsKb;

    /**
     * Application news enabled.
     *
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $appsNews;

    /**
     * Application downloads enabled.
     *
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $appsDownloads;

    /**
     * Application portal enabled.
     *
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $ifacePortal;

    /**
     * Application widget enabled.
     *
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $ifaceWidget;

    /**
     * Show ratings.
     *
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $showRatings;

    /**
     * Show ratings minimum votes.
     *
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $showRatingsMinVotes;

    /**
     * Publish comments.
     *
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $publishComments;

    /**
     * @return string
     */
    public function getSiteName()
    {
        return $this->siteName;
    }

    /**
     * @param string $siteName
     *
     * @return GeneralSettings
     */
    public function setSiteName($siteName)
    {
        $this->siteName = $siteName;

        return $this;
    }

    /**
     * @return string
     */
    public function getSiteUrl()
    {
        return $this->siteUrl;
    }

    /**
     * @param string $siteUrl
     *
     * @return GeneralSettings
     */
    public function setSiteUrl($siteUrl)
    {
        $this->siteUrl = $siteUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeskproName()
    {
        return $this->deskproName;
    }

    /**
     * @param string $deskproName
     *
     * @return GeneralSettings
     */
    public function setDeskproName($deskproName)
    {
        $this->deskproName = $deskproName;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeskproUrl()
    {
        return $this->deskproUrl;
    }

    /**
     * @param string $deskproUrl
     *
     * @return GeneralSettings
     */
    public function setDeskproUrl($deskproUrl)
    {
        $this->deskproUrl = $deskproUrl;

        return $this;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("portal_mode")
     *
     * @return string
     */
    public function getPortalMode()
    {
        if ($this->appsDownloads || $this->appsFeedback || $this->appsKb || $this->appsNews) {
            return 'publish';
        }

        return 'tickets';
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("brand_name")
     *
     * @return string
     */
    public function getBrandName()
    {
        return $this->brand->getName();
    }

    /**
     * @param string $brandName
     *
     * @return $this
     */
    public function setBrandName($brandName)
    {
        $this->brand->setName($brandName);

        return $this;
    }

    /**
     * @return bool
     */
    public function isAppsFeedback()
    {
        return $this->appsFeedback;
    }

    /**
     * @param bool $appsFeedback
     *
     * @return GeneralSettings
     */
    public function setAppsFeedback($appsFeedback)
    {
        $this->appsFeedback = $appsFeedback;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAppsKb()
    {
        return $this->appsKb;
    }

    /**
     * @param bool $appsKb
     *
     * @return GeneralSettings
     */
    public function setAppsKb($appsKb)
    {
        $this->appsKb = $appsKb;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAppsNews()
    {
        return $this->appsNews;
    }

    /**
     * @param bool $appsNews
     *
     * @return GeneralSettings
     */
    public function setAppsNews($appsNews)
    {
        $this->appsNews = $appsNews;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAppsDownloads()
    {
        return $this->appsDownloads;
    }

    /**
     * @param bool $appsDownloads
     *
     * @return GeneralSettings
     */
    public function setAppsDownloads($appsDownloads)
    {
        $this->appsDownloads = $appsDownloads;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIfacePortal()
    {
        return $this->ifacePortal;
    }

    /**
     * @param bool $ifacePortal
     *
     * @return GeneralSettings
     */
    public function setIfacePortal($ifacePortal)
    {
        $this->ifacePortal = $ifacePortal;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIfaceWidget()
    {
        return $this->ifaceWidget;
    }

    /**
     * @param bool $ifaceWidget
     *
     * @return GeneralSettings
     */
    public function setIfaceWidget($ifaceWidget)
    {
        $this->ifaceWidget = $ifaceWidget;

        return $this;
    }

    /**
     * @return bool
     */
    public function isShowRatings()
    {
        return $this->showRatings;
    }

    /**
     * @param bool $showRatings
     *
     * @return GeneralSettings
     */
    public function setShowRatings($showRatings)
    {
        $this->showRatings = $showRatings;

        return $this;
    }

    /**
     * @return int
     */
    public function getShowRatingsMinVotes()
    {
        return $this->showRatingsMinVotes;
    }

    /**
     * @param int $showRatingsMinVotes
     *
     * @return GeneralSettings
     */
    public function setShowRatingsMinVotes($showRatingsMinVotes)
    {
        $this->showRatingsMinVotes = $showRatingsMinVotes;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPublishComments()
    {
        return $this->publishComments;
    }

    /**
     * @param bool $publishComments
     *
     * @return GeneralSettings
     */
    public function setPublishComments($publishComments)
    {
        $this->publishComments = $publishComments;

        return $this;
    }
}
