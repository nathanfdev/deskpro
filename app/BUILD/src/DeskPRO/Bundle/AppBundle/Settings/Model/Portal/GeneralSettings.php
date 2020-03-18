<?php

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
     * Brand slug.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    private $brandSlug;

    /**
     * Application community enabled.
     *
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $appsCommunity;

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
     * Application guides enabled.
     *
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $appsGuides;

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
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $limitEmailDomains;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $limitEmailDomainsPatterns;

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
     * @return string
     */
    public function getBrandSlug()
    {
        return $this->brandSlug;
    }

    /**
     * @param string $brandSlug
     *
     * @return $this
     */
    public function setBrandSlug($brandSlug)
    {
        $this->brandSlug = $brandSlug;

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
        if ($this->appsDownloads || $this->appsCommunity || $this->appsKb || $this->appsNews || $this->appsGuides) {
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
    public function isAppsCommunity()
    {
        return $this->appsCommunity;
    }

    /**
     * @param bool $appsCommunity
     *
     * @return GeneralSettings
     */
    public function setAppsCommunity($appsCommunity)
    {
        $this->appsCommunity = $appsCommunity;

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
    public function isAppsGuides()
    {
        return $this->appsGuides;
    }

    /**
     * @param bool $appsGuides
     *
     * @return GeneralSettings
     */
    public function setAppsGuides($appsGuides)
    {
        $this->appsGuides = $appsGuides;

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

    /**
     * @return bool
     */
    public function isLimitEmailDomains()
    {
        return $this->limitEmailDomains;
    }

    /**
     * @param bool $limitEmailDomains
     *
     * @return $this
     */
    public function setLimitEmailDomains($limitEmailDomains)
    {
        $this->limitEmailDomains = $limitEmailDomains;

        return $this;
    }

    /**
     * @return string
     */
    public function getLimitEmailDomainsPatterns()
    {
        return $this->limitEmailDomainsPatterns;
    }

    /**
     * @param string $limitEmailDomainsPatterns
     *
     * @return $this
     */
    public function setLimitEmailDomainsPatterns($limitEmailDomainsPatterns)
    {
        $this->limitEmailDomainsPatterns = $limitEmailDomainsPatterns;

        return $this;
    }
}
