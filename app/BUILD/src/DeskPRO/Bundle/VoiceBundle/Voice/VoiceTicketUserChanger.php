<?php

namespace DeskPRO\Bundle\VoiceBundle\Voice;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use DeskPRO\Bundle\AppBundle\Entity\TicketMessageVoicePhoneCall;
use DeskPRO\Bundle\VoiceBundle\Helper\VoiceEventHelper;
use Doctrine\ORM\EntityManager;

/**
 * Class VoiceTicketUserChanger.
 */
class VoiceTicketUserChanger
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var VoiceEventHelper
     */
    private $voiceEventHelper;

    /**
     * Constructor.
     *
     * @param EntityManager    $em
     * @param VoiceEventHelper $voiceEventHelper
     */
    public function __construct(EntityManager $em, VoiceEventHelper $voiceEventHelper)
    {
        $this->em               = $em;
        $this->voiceEventHelper = $voiceEventHelper;
    }

    /**
     * @param Ticket $ticket
     */
    public function syncPhoneCallUser(Ticket $ticket)
    {
        $person  = $ticket->getPerson();
        $message = $this->getLastVoiceMessage($ticket);
        if (!$message) {
            return;
        }

        $phoneCall = $message->getActiveCall();
        if (!$phoneCall) {
            return;
        }

        $oldPerson = $phoneCall->getPerson();

        $message->setPerson($person);

        $phoneCall->setPerson($person);
        foreach ($phoneCall->getUserParticipants() as $participant) {
            if ($participant->getPerson() === $oldPerson) {
                $participant->setPerson($person);
            }
        }
        foreach ($phoneCall->getPhoneCallLogs() as $callLog) {
            if ($callLog->getPerson() === $oldPerson) {
                $callLog->setPerson($person);
            }
        }

        $this->em->flush();
        $this->voiceEventHelper->sendConferenceStatus($phoneCall);
    }

    /**
     * @param Ticket $ticket
     *
     * @return TicketMessage|null
     */
    public function getLastVoiceMessage(Ticket $ticket)
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('m')
            ->from(TicketMessage::class, 'm')
            ->join('m.attributes', 'a')
            ->where('m.ticket = :ticket')
            ->andWhere('a.name = :attr_name')
            ->setParameter('ticket', $ticket)
            ->setParameter('attr_name', TicketMessageVoicePhoneCall::ATTR_NAME)
            ->orderBy('m.id', 'desc')
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
