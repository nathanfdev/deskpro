<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Reports;

use Application\DeskPRO\Entity\ReportDashboardShareableShortUrl as ReportDashboardShareableShortUrlEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * Class ReportDashboardShareableShortUrl.
 */
class ReportDashboardShareableShortUrl
{
    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $authCode;

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateExpire;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $redirectUrl;

    /**
     * Constructor.
     *
     * @param ReportDashboardShareableShortUrlEntity $entity
     */
    public function __construct(ReportDashboardShareableShortUrlEntity $entity)
    {
        $this->id          = $entity->getId();
        $this->authCode    = $entity->getAuthCode();
        $this->dateCreated = $entity->getDateCreated();
        $this->dateExpire  = $entity->getDateExpire();
    }

    /**
     * @param string $redirectUrl
     */
    public function setRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;
    }
}
