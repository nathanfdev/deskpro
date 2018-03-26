<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Exception;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\AbstractStatefulIncident;

/**
 * Class AbstractExceptionIncident.
 */
abstract class AbstractExceptionIncident extends AbstractStatefulIncident
{
    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->getFirstEvent()->getSubjectDescription();
    }
}
