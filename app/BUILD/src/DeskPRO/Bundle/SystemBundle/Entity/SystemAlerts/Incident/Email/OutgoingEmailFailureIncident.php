<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Email;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\AbstractStatefulIncident;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class OutgoingEmailFailureIncident.
 *
 * @ORM\Entity
 */
class OutgoingEmailFailureIncident extends AbstractStatefulIncident
{
    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return "Continuing {$this->getFirstEvent()->getSubjectDescription()} outgoing email failures";
    }
}
