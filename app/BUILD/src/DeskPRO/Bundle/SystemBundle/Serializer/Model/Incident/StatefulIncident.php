<?php

namespace DeskPRO\Bundle\SystemBundle\Serializer\Model\Incident;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\StatefulIncident as StatefulIncidentEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * Class AbstractStatefulIncident.
 */
class StatefulIncident extends Incident
{
    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    protected $resolved = false;

    /**
     * {@inheritdoc}
     */
    public function __construct(StatefulIncidentEntity $incident)
    {
        parent::__construct($incident);
        $this->resolved = $incident->isResolved();
    }
}
