<?php

namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\DirectMessage;
use DeskPRO\Bundle\AppBundle\Entity\DirectMessageBlock;
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
     * @param bool $isUnread
     *
     * @param $page
     * @param $maxPerPage
     * @param bool $includesLatestMessage TRUE to include the latest message content
     * @return Pagerfanta
     */
    public function getForUser(Person $user, $isUnread, $page, $maxPerPage, $includesLatestMessage = false)
    {
        $qb    = $this->em->getRepository(DirectMessageThread::class)->getForUser($user, $isUnread, true, $includesLatestMessage);
        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager->setMaxPerPage($maxPerPage);
        $pager->setCurrentPage($page);

        return $pager;
    }

    /**
     * @param DirectMessageThread[] $threads
     *
     * @param null|int $entityIndex
     * @return array
     */
    public function getParticipantsGroupedByThreads($threads, $entityIndex = null)
    {
        return $this->em->getRepository(DirectMessageParticipant::class)->getGroupedForThreads($threads, $entityIndex);
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

                $this->assertNotBeingBlocked(
                    $thread,
                    $personFrom
                );

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
     * @param Person $sender
     * @throws \Exception
     */
    public function saveMessage(DirectMessage $message, Person $sender)
    {
        $this->assertNotBeingBlocked(
            $message->getAuthor()->getThread(),
            $sender
        );

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

    /**
     * @param DirectMessageThread $thread
     * @param Person $sender
     */
    private function assertNotBeingBlocked(DirectMessageThread $thread, Person $sender)
    {
        $isBeingBlocked = $this
            ->em
            ->getRepository(DirectMessageBlock::class)
            ->isBlockedByThread($thread, $sender)
        ;

        if ($isBeingBlocked) {
            throw new \DomainException('Cannot send message to user as either you are blocking this user or '
                .'they are blocking you');
        }
    }
}
