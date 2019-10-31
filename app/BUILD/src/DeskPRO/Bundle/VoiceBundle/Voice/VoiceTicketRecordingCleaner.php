<?php

namespace DeskPRO\Bundle\VoiceBundle\Voice;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketLog;
use Application\DeskPRO\Entity\TicketMessage;
use DeskPRO\Bundle\AppBundle\Entity\TicketMessageVoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallLog;
use Doctrine\ORM\EntityManager;

/**
 * Class VoiceTicketRecordingCleaner.
 */
class VoiceTicketRecordingCleaner
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var DeskproBlobStorage
     */
    private $blobStorage;

    /**
     * Constructor.
     *
     * @param EntityManager      $em
     * @param DeskproBlobStorage $blobStorage
     */
    public function __construct(EntityManager $em, DeskproBlobStorage $blobStorage)
    {
        $this->em          = $em;
        $this->blobStorage = $blobStorage;
    }

    /**
     * @param Ticket $ticket
     */
    public function deleteTicketRecordings(Ticket $ticket)
    {
        foreach ($ticket->getMessages() as $message) {
            if ($message->isAgentNote()) {
                $this->deleteTicketMessageRecordings($message);
            }
        }
    }

    /**
     * @param TicketMessage $message
     */
    public function deleteTicketMessageRecordings(TicketMessage $message)
    {
        /** @var TicketMessageVoicePhoneCall $ticketMessageVoicePhoneCall */
        $ticketMessageVoicePhoneCall = $message->getAttribute(TicketMessageVoicePhoneCall::ATTR_NAME);
        if (!$ticketMessageVoicePhoneCall) {
            return;
        }

        $phoneCall = $ticketMessageVoicePhoneCall->getPhoneCall();
        foreach ($phoneCall->getTempRecordings() as $recording) {
            $phoneCall->removeRecording($recording);
        }

        $fullRecording = $phoneCall->getFullRecording();

        if ($fullRecording) {
            $phoneCall->removeRecording($fullRecording);

            $ticketLog = new TicketLog();
            $ticketLog
                ->setTicket($message->getTicket())
                ->setIdObject($phoneCall->getId())
                ->setActionType(VoicePhoneCallLog::ACTION_RECORDING_DELETED)
            ;

            if ($fullRecording->getBlob()) {
                $ticketLog->setDetailItem('filesize', $fullRecording->getBlob()->getFilesize() / 1024);
                $this->blobStorage->deleteBlobRecord($fullRecording->getBlob());
            }

            $this->em->persist($ticketLog);

            $callLog = new VoicePhoneCallLog();
            $callLog->setPhoneCall($phoneCall);
            $callLog->setActionType(VoicePhoneCallLog::ACTION_RECORDING_DELETED);

            $this->em->persist($callLog);
        }

        $this->em->flush();
    }
}
