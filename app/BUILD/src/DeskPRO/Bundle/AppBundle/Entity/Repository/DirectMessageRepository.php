<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Repository;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\AbstractEntityRepository;
use DeskPRO\Bundle\AppBundle\Entity\DirectMessageThread;

/**
 * Class DirectMessageRepository.
 */
class DirectMessageRepository extends AbstractEntityRepository
{
    /**
     * @param DirectMessageThread $thread
     *
     * @return DirectMessage[]
     */
    public function getForThread(DirectMessageThread $thread)
    {
        $qb = $this->createQueryBuilder('dm_message');
        $qb
            ->addSelect('dm_participant, person')
            ->innerJoin('dm_message.author', 'dm_participant')
            ->leftJoin('dm_participant.person', 'person')
            ->where('dm_participant.thread = :thread')
            ->orderBy('dm_message.dateCreated', 'ASC')
            ->setParameter('thread', $thread);

        return $qb->getQuery()->getResult();
    }
}
