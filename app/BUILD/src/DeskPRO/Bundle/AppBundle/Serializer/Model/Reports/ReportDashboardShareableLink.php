<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Reports;

use Application\DeskPRO\Entity\ReportDashboardShareableLink as ReportDashboardShareableLinkEntity;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use JMS\Serializer\Annotation as JMS;

/**
 * Class ReportDashboardShareableLink.
 */
class ReportDashboardShareableLink
{
    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * Tab title.
     *
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
    private $authCode;

    /**
     * @JMS\Type("entity<Application\DeskPRO\Entity\ReportDashboard>")
     *
     * @var ReportDashboard
     */
    private $dashboard;

    /**
     * @JMS\Type("entity<Application\DeskPRO\Entity\ReportDashboardReport>")
     *
     * @var ReportDashboardReport
     */
    private $defaultReport;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $whoCanUse;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $ipWhitelist = [];

    /**
     * @JMS\Type("deferred<Application\DeskPRO\Entity\ReportDashboardShareableShortUrl>")
     *
     * @var CallbackDeferredProperty
     */
    private $shortUrl;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $permalink;

    /**
     * Constructor.
     *
     * @param ReportDashboardShareableLinkEntity $entity
     */
    public function __construct(ReportDashboardShareableLinkEntity $entity)
    {
        $this->id            = $entity->getId();
        $this->title         = $entity->getTitle();
        $this->authCode      = $entity->getAuthCode();
        $this->dashboard     = $entity->getDashboard();
        $this->defaultReport = $entity->getDefaultReport();
        $this->whoCanUse     = $entity->getWhoCanUse();
        $this->ipWhitelist   = $entity->getIpWhitelist();
    }

    /**
     * @param CallbackDeferredProperty $shortUrl
     *
     * @return $this
     */
    public function setShortUrl($shortUrl = null)
    {
        $this->shortUrl = $shortUrl;

        return $this;
    }

    /**
     * @param string $permalink
     */
    public function setPermalink($permalink)
    {
        $this->permalink = $permalink;
    }
}
