<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\PHP;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\AbstractStatefulIncident;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class PhpCriticalErrorIncident.
 *
 * @ORM\Entity
 */
class PhpCriticalErrorIncident extends AbstractStatefulIncident
{
    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->getFirstEvent()->getSubjectDescription();
    }
}
