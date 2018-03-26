<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Repository;

use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\EntityRepository\AbstractEntityRepository;

/**
 * Class SnippetUseLogRepository.
 */
class SnippetUseLogRepository extends AbstractEntityRepository
{
    /**
     * @param TicketMessage $ticketMessage
     *
     * @return array
     */
    public function getLogsByTicketMessage(TicketMessage $ticketMessage)
    {
        $qb = $this->createQueryBuilder('s');
        $qb
            ->where('s.ticketMessage = :ticketMessage')
            ->setParameter('ticketMessage', $ticketMessage)
        ;

        return $qb->getQuery()->getResult();
    }
}
