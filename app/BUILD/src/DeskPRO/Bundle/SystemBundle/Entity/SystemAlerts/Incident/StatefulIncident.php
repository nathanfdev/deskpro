<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident;

/**
 * Interface StatefulIncident.
 */
interface StatefulIncident extends Incident
{
    /**
     * @return bool
     */
    public function isResolved();

    /**
     * @param bool $resolved
     */
    public function setResolved($resolved);
}
