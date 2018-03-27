<?php

namespace DeskPRO\Bundle\SystemBundle\Serializer\Model\Incident;

use JMS\Serializer\Annotation as JMS;

/**
 * Class IncidentInstructions.
 */
class IncidentInstructions
{
    /**
     * @JMS\Type("DeskPRO\Bundle\SystemBundle\Serializer\Model\Incident\Incident")
     *
     * @var Incident
     */
    private $incident;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $instructionsHtml;

    /**
     * Constructor.
     *
     * @param Incident $incident
     * @param string   $instructionsHtml
     */
    public function __construct(Incident $incident, $instructionsHtml)
    {
        $this->incident         = $incident;
        $this->instructionsHtml = $instructionsHtml;
    }
}
