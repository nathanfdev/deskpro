<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\PHP;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\AbstractStatefulIncident;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class PhpNoticeIncident.
 *
 * @ORM\Entity
 */
class PhpNoticeIncident extends AbstractStatefulIncident
{
    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->getFirstEvent()->getSubjectDescription();
    }
}
