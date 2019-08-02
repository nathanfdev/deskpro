<?php

namespace DeskPRO\Bundle\VoiceBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\VoiceBundle\Voice\VoiceTicketUserChanger;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

/**
 * Class VoiceTicketListener.
 */
class VoiceTicketListener implements EventSubscriber
{
    /**
     * @var VoiceTicketUserChanger
     */
    private $userChanger;

    /**
     * @var array
     */
    private $updateQueue = [];

    /**
     * Constructor.
     *
     * @param VoiceTicketUserChanger $userChanger
     */
    public function __construct(VoiceTicketUserChanger $userChanger)
    {
        $this->userChanger = $userChanger;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'postFlush',
            'onClear',
        ];
    }

    /**
     * @param Ticket             $ticket
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(Ticket $ticket, PreUpdateEventArgs $args)
    {
        if ($args->hasChangedField('person')) {
            $this->updateQueue[] = $ticket;
        }
    }

    public function onClear()
    {
        $this->updateQueue = [];
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if ($this->updateQueue) {
            $em = $args->getEntityManager();

            /** @var Ticket $ticket */
            foreach ($this->updateQueue as $num => $ticket) {
                array_splice($this->updateQueue, $num, 1);
                $em->getUnitOfWork()->clearEntityChangeSet(spl_object_hash($ticket));

                $this->userChanger->syncPhoneCallUser($ticket);
            }
        }
    }
}
