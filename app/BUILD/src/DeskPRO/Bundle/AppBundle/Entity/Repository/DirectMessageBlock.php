<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Repository;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\DirectMessageThread;
use Doctrine\ORM\EntityRepository;

/**
 * Class DirectMessageBlock
 *
 * @package DeskPRO\Bundle\AppBundle\Entity\Repository
 */
class DirectMessageBlock extends EntityRepository
{
    /**
     * @param DirectMessageThread $thread
     * @param Person $sender
     * @return bool TRUE if blocked
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isBlockedByThread(DirectMessageThread $thread, Person $sender)
    {
        $recipientId = current(array_filter($thread->getParticipantIds(), function ($id) use ($sender) {
            return $id != $sender->getId();
        }));

        $query = $this->createQueryBuilder('b')
            ->select('CASE WHEN COUNT(b.id) > 0 THEN true ELSE false END')
            ->andWhere('(b.person = :sender AND IDENTITY(b.byPerson) = :recipientId) OR (IDENTITY(b.person) = :recipientId AND b.byPerson = :sender)')
            ->setParameter('recipientId', $recipientId)
            ->setParameter('sender', $sender)
            ->getQuery()
        ;

        return $query->getSingleScalarResult();
    }

    /**
     * @param DirectMessageThread $thread
     * @param Person $blocker
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function unblockIfUserIsBlocker(DirectMessageThread $thread, Person $blocker)
    {
        $recipientId = current(array_filter($thread->getParticipantIds(), function ($id) use ($blocker) {
            return $id != $blocker->getId();
        }));

        $query = $this->createQueryBuilder('b')
            ->select('b')
            ->andWhere('IDENTITY(b.person) = :recipientId AND b.byPerson = :blocker')
            ->setParameter('recipientId', $recipientId)
            ->setParameter('blocker', $blocker)
            ->getQuery()
        ;

        $block = $query->getOneOrNullResult();

        if (!$block) {
            throw new \DomainException('Cannot unblock as you are being blocked by the other user');
        }

        $em = $this->getEntityManager();
        $em->remove($block);
        $em->flush();
    }
}
