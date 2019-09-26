<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Repository;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\AbstractEntityRepository;

/**
 * Class DirectMessageParticipantRepository.
 */
class DirectMessageParticipantRepository extends AbstractEntityRepository
{
    /**
     * @param Person $user
     * @param bool   $isUnread
     *
     * @return DirectMessageThread[]
     */
    public function getGroupedForThreads($threads, $entityIndex = null)
    {
        if (!count($threads)) {
            return [];
        }

        $ids = [];
        foreach ($threads as $thread) {
            if (null !== $entityIndex) {
                $ids[] = $thread[$entityIndex]->getId();
            } else {
                $ids[] = $thread->getId();
            }
        }

        $qb = $this->createQueryBuilder('dm_participant');
        $qb
            ->addSelect('person')
            ->leftJoin('dm_participant.person', 'person')
            ->where($qb->expr()->in('dm_participant.thread', $ids))
        ;

        $participant = $qb->getQuery()->getResult();

        $res = [];
        foreach ($threads as $thread) {
            if (null !== $entityIndex) {
                if (!isset($res[$thread[$entityIndex]->getId()])) {
                    $res[$thread[$entityIndex]->getId()] = [];
                }
            } else {
                if (!isset($res[$thread->getId()])) {
                    $res[$thread->getId()] = [];
                }
            }
        }
        foreach ($participant as $participant) {
            $res[$participant->getThread()->getId()][] = $participant;
        }

        return $res;
    }
}
