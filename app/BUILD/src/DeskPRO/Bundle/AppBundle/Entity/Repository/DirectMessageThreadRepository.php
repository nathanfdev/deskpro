<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Repository;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\AbstractEntityRepository;
use DeskPRO\Bundle\AppBundle\Entity\DirectMessage;
use DeskPRO\Bundle\AppBundle\Entity\DirectMessageParticipant;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class DirectMessageThreadRepository.
 */
class DirectMessageThreadRepository extends AbstractEntityRepository
{
    /**
     * @param Person $user
     * @param bool   $isUnread
     *
     * @return DirectMessageThread[]
     */
    public function getForUser(Person $user, $isUnread = false, $isReturnQb = false, $includesLatestMessage = false)
    {
        $qb = $this->createQueryBuilder('dm_thread');
        $qb->innerJoin(
                'AppBundle:DirectMessageParticipant',
                'dm_part',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'dm_thread = dm_part.thread'
            )
            ->where('dm_part.person = :person')
            ->orderBy('dm_thread.dateCreated', 'DESC')
            ->setParameter('person', $user);

        if ($isUnread) {
            $qb->andWhere('dm_part.isUnread = true');
        }

        if ($includesLatestMessage) {
            $latestMessageDql = $this->getEntityManager()->createQueryBuilder()
                ->select('m.messageHtml')
                ->from(DirectMessage::class, 'm')
                ->innerJoin('m.author', 'p')
                ->andWhere('p.thread = dm_thread')
                ->orderBy('m.dateCreated', 'DESC')
                ->setMaxResults(1)
            ;

            $qb
                ->addSelect(sprintf(
                    'FIRST(%s) AS latestMessage',
                    $latestMessageDql->getDQL()
                ))
            ;
        }

        if ($isReturnQb) {
            return $qb;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Person $personA
     * @param Person $personB
     *
     * @return DirectMessageThread
     */
    public function getOneForUsers(Person $personA, Person $personB)
    {
        $qb = $this->createQueryBuilder('dm_thread');
        $qb->innerJoin(
                'AppBundle:DirectMessageParticipant',
                'dm_part_a',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'dm_thread = dm_part_a.thread'
            )
            ->innerJoin(
                'AppBundle:DirectMessageParticipant',
                'dm_part_b',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'dm_thread = dm_part_b.thread'
            )
            ->where('dm_part_a.person = :personA')
            ->andWhere('dm_part_b.person = :personB')
            ->setParameters([
                'personA' => $personA,
                'personB' => $personB,
            ]);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
