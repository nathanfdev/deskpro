<?php

namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\DirectMessage;
use DeskPRO\Bundle\AppBundle\Entity\DirectMessageParticipant;
use DeskPRO\Bundle\AppBundle\Entity\DirectMessageThread;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * Class DirectMessageThreadDataService.
 */
class DirectMessageThreadDataService extends AbstractDataService
{
    /**
     * @TODO: return
     *
     * @param Person $user
     * @param bool   $isUnread
     *
     * @return DirectMessageThread[]
     */
    public function getForUser(Person $user, $isUnread, $page, $maxPerPage)
    {
        $qb    = $this->em->getRepository(DirectMessageThread::class)->getForUser($user, $isUnread, true);
        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager->setMaxPerPage($maxPerPage);
        $pager->setCurrentPage($page);

        return $pager;
    }

    /**
     * @param DirectMessageThread[] $threads
     *
     * @return array
     */
    public function getParticipantsGroupedByThreads($threads)
    {
        return $this->em->getRepository(DirectMessageParticipant::class)->getGroupedForThreads($threads);
    }

    /**
     * @param Person $personFrom
     * @param Person $personTo
     *
     * @return DirectMessageThread
     */
    public function createThread(Person $personFrom, Person $personTo)
    {
        $thread = $this->em->getRepository(DirectMessageThread::class)->getOneForUsers($personFrom, $personTo);

        if (!$thread) {
            $this->em->beginTransaction();
            try {
                $thread = new DirectMessageThread();

                $participantFrom = new DirectMessageParticipant();
                $participantFrom->setPerson($personFrom);
                $participantFrom->setThread($thread);

                $participantTo = new DirectMessageParticipant();
                $participantTo->setPerson($personTo);
                $participantTo->setIsUnread(true);
                $participantTo->setThread($thread);

                $this->em->persist($thread);
                $this->em->persist($participantFrom);
                $this->em->persist($participantTo);
                $this->em->flush();

                $thread->addParticipantId($participantFrom->getPerson()->getId());
                $thread->addParticipantId($participantTo->getPerson()->getId());
                $this->em->flush();

                $this->em->commit();
            } catch (\Exception $e) {
                $this->em->rollback();
                throw $e;
            }
        }

        return $thread;
    }

    /**
     * @param DirectMessage $message
     */
    public function saveMessage(DirectMessage $message)
    {
        $this->em->beginTransaction();

        try {
            // @TODO: could be moved to listener
            $query = $this->em->createQuery('
                UPDATE AppBundle:DirectMessageParticipant p
                SET p.isUnread = true
                WHERE p.thread = :thread
                AND p.id != :participant
            ');
            $query->setParameters([
                'thread'      => $message->getAuthor()->getThread(),
                'participant' => $message->getAuthor()->getId(),
            ]);
            $query->execute();

            $message->getAuthor()->getThread()->setDateLastMessage(new \DateTime('now'));

            $this->em->persist($message);
            $this->em->flush();

            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }
}
