<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2011 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Abdullah Kiser <kiser.bd@gmail.com>
 */

namespace Application\DeskPRO\EntityRepository;

use Symfony\Component\Validator\Constraints\DateTime;

use Application\DeskPRO\App;
use \Doctrine\ORM\EntityRepository;
use Application\DeskPRO\Entity;

class DealStage extends EntityRepository
{
    public function getDealStagesByDealType($deal_type_id)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('dts, ds')
                ->from('DeskPRO:DealStage', 'ds')
                ->innerJoin('ds.deal_type_stage', 'dts')
                ->innerJoin('dts.deal_type', 'dt')
                ->where('dt.id = :deal_type_id');
        $qb->setParameter('deal_type_id', $deal_type_id);

        $query = $qb->getQuery(); //print $query->getSQL(); exit;
        return $query->getResult();
    }
}