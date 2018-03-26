<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Email;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\AbstractStatefulIncident;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class IncomingEmailFailureIncident.
 *
 * @ORM\Entity
 */
class IncomingEmailFailureIncident extends AbstractStatefulIncident
{
    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        $description = $this->getFirstEvent() ? $this->getFirstEvent()->getSubjectDescription() : '';

        return "Continuing $description incoming email failures";
    }

    /**
     * @return int
     */
    public function getEmailAccountId()
    {
        return  $this->getFirstEvent() ? $this->getFirstEvent()->getEmailAccountId() : null;
    }
}
