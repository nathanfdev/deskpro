<?php

namespace DeskPRO\Bundle\SystemBundle\Serializer\Model\Incident;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Incident as IncidentEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Incident.
 */
class Incident
{
    /**
     * @var int
     *
     * @JMS\Type("integer")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime")
     */
    protected $dateCreated;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    protected $raised = false;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    protected $dismissed = false;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $title;

    /**
     * Constructor.
     *
     * @param IncidentEntity $incident
     */
    public function __construct(IncidentEntity $incident)
    {
        $this->id          = $incident->getId();
        $this->dateCreated = $incident->getDateCreated();
        $this->raised      = $incident->isRaised();
        $this->dismissed   = $incident->isDismissed();
        $this->title       = $incident->getTitle();
    }
}
