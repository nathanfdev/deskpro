<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\Entity\Sla;
use Application\DeskPRO\EntityRepository\Sla as SlaRepo;

/**
 * Class SlasDataService.
 *
 * @deprecated Looks like we do not really
 */
class SlasDataService extends AbstractDataService
{
    /**
     * @return Sla[]
     */
    public function loadAll()
    {
        return $this->getRepo()->findAll();
    }

    /**
     * @param $sla_id
     *
     * @return null|Sla
     */
    public function loadSingle($sla_id)
    {
        return $this->getRepo()->findOneBy(['id' => $sla_id]);
    }

    /**
     * @return SlaRepo
     */
    public function getRepo()
    {
        return $this->em->getRepository('DeskPRO:Sla');
    }
}
