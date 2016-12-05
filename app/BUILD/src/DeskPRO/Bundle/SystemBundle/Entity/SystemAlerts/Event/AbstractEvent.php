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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event;

use DeskPRO\Bundle\AppBundle\Entity\NotifyPropertyChangedTrait;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class AbstractEvent.
 *
 * @ORM\Entity
 * @ORM\Table(name="system_alerts_events")
 * @ORM\ChangeTrackingPolicy("DEFERRED_IMPLICIT")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string", length=30)
 * @ORM\DiscriminatorMap({
 *     "jira_api_exception"     = "DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Exception\JiraApiExceptionEvent",
 *     "oauth_exception"        = "DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Exception\OAuthExceptionEvent",
 *     "generic_exception"      = "DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Exception\ExceptionEvent",
 *     "email_incoming_failure" = "DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\IncomingEmailFailureEvent",
 *     "email_incoming_success" = "DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\IncomingEmailSuccessEvent",
 *     "email_outgoing_failure" = "DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\OutgoingEmailFailureEvent",
 *     "email_outgoing_success" = "DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email\OutgoingEmailSuccessEvent",
 *     "php_error"              = "DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\PHP\ErrorEvent"
 * })
 * @JMS\ExclusionPolicy("all")
 */
abstract class AbstractEvent implements Event
{
    use NotifyPropertyChangedTrait;

    const EXPIRES_WITH_TIME     = 'time';
    const EXPIRES_WITH_QUANTITY = 'quantity';

    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="subject_unique_id", type="string")
     */
    protected $subjectUniqueId;

    /**
     * @var \DateTime
     * @ORM\Column(name="date_created", type="datetime", nullable=false)
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     */
    protected $dateCreated;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     */
    protected $processed = false;

    /**
     * @var string
     *
     * @ORM\Column(name="expiration_strategy", type="string")
     */
    protected $expirationStrategy = self::EXPIRES_WITH_QUANTITY;

    /**
     * Event constructor.
     *
     * @param \DateTime|null $dateCreated
     */
    public function __construct(\DateTime $dateCreated = null)
    {
        $this->dateCreated     = $dateCreated ?: new \DateTime();
        $this->subjectUniqueId = (string) $this->generateSubjectUniqueId();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubjectUniqueId()
    {
        return $this->subjectUniqueId;
    }

    /**
     * {@inheritdoc}
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * {@inheritdoc}
     */
    public function setDateCreated(\DateTime $date)
    {
        $this->dateCreated = $date;
    }

    /**
     * {@inheritdoc}
     */
    public function isProcessed()
    {
        return $this->processed;
    }

    /**
     * {@inheritdoc}
     */
    public function setProcessed($processed)
    {
        $this->processed = $processed;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getSubjectDescription();
    }

    /**
     * Generate subject unique ID.
     *
     * @see \DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Event::getSubjectUniqueId()
     *
     * @return string
     */
    abstract protected function generateSubjectUniqueId();
}
