<?php

namespace DeskPRO\Bundle\VoiceBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\Ticket;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class VoiceTicketListener.
 */
class VoiceTicketListener implements EventSubscriber
{
    const SYNC_USER                      = 'sync_user';
    const TYPE_DELETE_TICKET_RECORDINGS  = 'delete_ticket_recordings';
    const TYPE_DELETE_MESSAGE_RECORDINGS = 'delete_message_recordings';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $updateQueue = [];

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
     * @internal
     *
     * @param Ticket             $ticket
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(Ticket $ticket, PreUpdateEventArgs $args)
    {
        if ($args->hasChangedField('person')) {
            $this->updateQueue[] = [
                'type'   => self::SYNC_USER,
                'ticket' => $ticket,
            ];
        }
        if ($args->hasChangedField('messages')) {
            $changeRecorder = $ticket->getStateChangeRecorder();
            foreach ($changeRecorder->getChanges() as $change) {
                if ($change->getField() === 'message' && $change->getNew() === null && $change->getOld()) {
                    $this->updateQueue[] = [
                        'type'    => self::TYPE_DELETE_MESSAGE_RECORDINGS,
                        'ticket'  => $ticket,
                        'message' => $change->getOld(),
                    ];
                }
            }
        }
    }

    /**
     * @internal
     *
     * @param Ticket $ticket
     */
    public function preRemove(Ticket $ticket)
    {
        $this->updateQueue[] = [
            'type'   => self::TYPE_DELETE_TICKET_RECORDINGS,
            'ticket' => $ticket,
        ];
    }

    /**
     * @internal
     */
    public function onClear()
    {
        $this->updateQueue = [];
    }

    /**
     * @internal
     *
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if ($this->updateQueue) {
            foreach ($this->updateQueue as $num => $change) {
                array_splice($this->updateQueue, $num, 1);

                /** @var Ticket $ticket */
                $ticket = $change['ticket'];

                // clear change set to avoid pre-update recursion
                $args->getEntityManager()->getUnitOfWork()->clearEntityChangeSet(spl_object_hash($ticket));

                $type = $change['type'];
                if ($type === self::SYNC_USER) {
                    $this->container->get('dp.voice.ticket_user_changer')->syncPhoneCallUser($ticket);
                } elseif ($type === self::TYPE_DELETE_TICKET_RECORDINGS) {
                    $this->container->get('dp.voice.ticket_recording_cleaner')->deleteTicketRecordings($ticket);
                } elseif ($type === self::TYPE_DELETE_MESSAGE_RECORDINGS) {
                    $this->container->get('dp.voice.ticket_recording_cleaner')->deleteTicketMessageRecordings($change['message']);
                }
            }
        }
    }
}
