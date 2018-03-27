<?php

namespace DeskPRO\Bundle\SystemBundle\Serializer\Handler\Entity;

use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\AbstractIncident;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Email\IncomingEmailFailureIncident;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Email\OutgoingEmailFailureIncident;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Exception\ExceptionIncident;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\PHP\PhpCriticalErrorIncident;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\PHP\PhpNoticeIncident;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\StatefulIncident;
use DeskPRO\Bundle\SystemBundle\Serializer\Model\Incident\Incident as IncidentModel;
use DeskPRO\Bundle\SystemBundle\Serializer\Model\Incident\IncidentInstructions;
use DeskPRO\Bundle\SystemBundle\Serializer\Model\Incident\StatefulIncident as StatefulIncidentModel;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\Instructions\InstructionsGenerator;

/**
 * Class IncidentHandler.
 */
class IncidentHandler extends AbstractEntityHandler
{
    /**
     * @var InstructionsGenerator
     */
    private $instructionsGenerator;

    /**
     * Constructor.
     *
     * @param InstructionsGenerator $instructionsGenerator
     */
    public function __construct(InstructionsGenerator $instructionsGenerator)
    {
        $this->instructionsGenerator = $instructionsGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return [
            IncomingEmailFailureIncident::class,
            OutgoingEmailFailureIncident::class,
            ExceptionIncident::class,
            PhpCriticalErrorIncident::class,
            PhpNoticeIncident::class,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @param AbstractIncident $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $instructionsHtml = $this->instructionsGenerator->generate($entity);

        if ($entity instanceof StatefulIncident) {
            $incidentModel = new StatefulIncidentModel($entity, $instructionsHtml);
        } else {
            $incidentModel = new IncidentModel($entity, $instructionsHtml);
        }

        return new IncidentInstructions($incidentModel, $instructionsHtml);
    }
}
