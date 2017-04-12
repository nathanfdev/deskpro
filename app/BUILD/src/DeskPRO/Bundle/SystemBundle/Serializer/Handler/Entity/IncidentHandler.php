<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
