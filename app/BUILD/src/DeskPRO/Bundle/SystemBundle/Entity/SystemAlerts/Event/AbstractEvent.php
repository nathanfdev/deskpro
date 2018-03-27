<?php

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
