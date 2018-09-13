<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketLog;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use DeskPRO\Bundle\AppBundle\Entity\TicketMessageVoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallLog;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use Orb\Util\CheckedOptionsArray;

/**
 * Delete all phone call records for this ticket matching options.
 */
class DeleteVoicePhoneCallRecords extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface
{
    /**
     * {@inheritdoc}
     */
    protected function getOptionsDef()
    {
        $options = new CheckedOptionsArray();
        $options->addValidNames(
            'at_least',
            'shorter_than',
            'longer_than'
        );

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $options = $this->getActionOptions();
        $em      = $this->getContainer()->getEm();

        foreach ($ticket->getMessages() as $message) {
            if ($ticketMessageVoicePhoneCall = $message->getAttribute('voice_phone_call')) {
                /** @var TicketMessageVoicePhoneCall $ticketMessageVoicePhoneCall */
                $voicePhoneCall = $ticketMessageVoicePhoneCall->getPhoneCall();
                /** @var VoicePhoneCall $voicePhoneCall */
                $recording = $voicePhoneCall->getRecording();
                if ($recording) {
                    if ($voicePhoneCall->getDuration() < $options['longer_than'] && $voicePhoneCall->getDuration() > $options['shorter_than']) {
                        continue;
                    }

                    if ($recording->getFilesize() / 1024 <= $options['at_least']) {
                        continue;
                    }

                    $voicePhoneCall->setRecording(null);
                    $em->persist($voicePhoneCall);

                    $ticketLog = new TicketLog();
                    $ticketLog
                        ->setTicket($ticket)
                        ->setIdObject($voicePhoneCall->getId())
                        ->setActionType(VoicePhoneCallLog::ACTION_RECORDING_DELETED)
                        ->setDetailItem('filesize', $recording->getFilesize() / 1024);
                    if ($context->getPersonContext()) {
                        $ticketLog->setPerson($context->getPersonContext());
                    }
                    $em->persist($ticketLog);

                    $this->getContainer()->get('blob.storage')->deleteBlobRecord($recording);
                    $em->flush();

                    $serializedData = $this->getContainer()->get('serializer')->toArray(
                        new ApiWrapper($voicePhoneCall),
                        new SideloadSerializationContext()
                    );

                    $this->getContainer()->get('event_dispatcher')->dispatch(
                        LegacySystemEvent::EVENT_NAME,
                        new LegacySystemEvent(
                            'agent.voice.recording_status',
                            ['data' => $serializedData]
                        )
                    );
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function applyMacro(Person $person, Ticket $ticket, ExecutorContextInterface $context)
    {
        $this->applyAction($ticket, $context);
    }
}
