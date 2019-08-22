<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Repository;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\AbstractEntityRepository;
use DeskPRO\Bundle\AppBundle\Entity\Approval\AbstractBaseApproval;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ApprovalRepository
 *
 * @package DeskPRO\Bundle\AppBundle\Entity\Repository
 */
class ApprovalRepository extends AbstractEntityRepository
{
    /**
     * @param AbstractBaseApproval $approval
     * @return Person[]
     */
    public function getApproversFromApproval(AbstractBaseApproval $approval)
    {
        return $this->buildGetApproversFromApproval($approval)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param AbstractBaseApproval $approval
     * @return QueryBuilder
     */
    private function buildGetApproversFromApproval(AbstractBaseApproval $approval)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('p, pe')
            ->from(Person::class, 'p')
            ->innerJoin('p.primary_email', 'pe')
            ->andWhere('p.is_deleted = FALSE AND p.is_disabled = FALSE')
            ->andWhere('p.id IN (:approverIds)')
            ->setParameter('approverIds', $approval->getApprovers(), Connection::PARAM_INT_ARRAY)
        ;
    }
}
