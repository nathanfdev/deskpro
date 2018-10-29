<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\App;
use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketAccessCode;
use Application\DeskPRO\Entity\TicketLog;
use DeskPRO\Bundle\AppBundle\Entity\TicketMessageVoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallLog;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\EntityManager;
use Orb\Util\Arrays;

class Util
{
    private function __construct()
    {
    }

    /**
     * Resolves a standard "agent codes" array into a set of agent IDs.
     *
     * If the codes array includes ticket context codes like 'assigned agent',
     * then $ticket should be sent as well. Note that permissions are NOT checked
     * here.
     *
     * @param $codes
     * @param \Application\DeskPRO\Entity\Ticket $ticket
     *
     * @return int[]
     */
    public static function resolveAgentCodes(array $codes, Ticket $ticket = null)
    {
        $agent_ids = [];

        foreach ($codes as $send_to) {
            if ($send_to == 'assigned_agent') {
                if ($ticket && $ticket->getAgentId()) {
                    $agent_ids[] = $ticket->getAgentId();
                }
            } elseif ($send_to == 'assigned_agent_team') {
                if ($ticket && $ticket->getAgentTeamId()) {
                    $agent_ids = array_merge(
                        $agent_ids,
                        App::getEntityRepository('DeskPRO:AgentTeam')->getMemberIds($ticket->getAgentTeamId())
                    );
                }
            } elseif ($send_to == 'all_agents') {
                $agents = App::getEntityRepository('DeskPRO:Person')->getAgents();
                foreach ($agents as $a) {
                    $agent_ids[] = $a->getId();
                }

                // Cant possibly add any more, so no need to continue looping
                break;
            } elseif (strpos($send_to, 'agent.') === 0) {
                list(, $agent_id) = explode('.', $send_to, 2);

                $agents = App::getEntityRepository('DeskPRO:Person')->getAgents();
                if (isset($agents[$agent_id])) {
                    $agent_ids[] = $agent_id;
                }
            } elseif (strpos($send_to, 'agent_team.') === 0) {
                list(, $agent_team_id) = explode('.', $send_to, 2);
                $agent_ids             = array_merge(
                    $agent_ids,
                    App::getEntityRepository('DeskPRO:AgentTeam')->getMemberIds($agent_team_id)
                );
            }
        }

        $agent_ids = array_unique($agent_ids);
        $agent_ids = Arrays::removeFalsey($agent_ids);

        return $agent_ids;
    }

    /**
     * Get a TAC for a person on a ticket. If an existing TAC doesn't exist,
     * a new one will be created automatically.
     *
     * @param \Application\DeskPRO\Entity\Ticket $ticket
     * @param \Application\DeskPRO\Entity\Person $person
     *
     * @return \Application\DeskPRO\Entity\TicketAccessCode
     */
    public static function getTacForPerson(Ticket $ticket, Person $person)
    {
        $em = App::getOrm();

        try {
            $tac = $em->createQuery('
                SELECT t
                FROM DeskPRO:TicketAccessCode t
                WHERE t.ticket = ?1 AND t.person = ?2
            ')->setParameters([1 => $ticket, 2 => $person])->getSingleResult();

            return $tac;
        } catch (\Exception $e) {
            $tac = new TicketAccessCode();
            $tac->setTicket($ticket);
            $tac->setPerson($person);
            $ticket->getAccessCodes()->add($tac);

            $em->persist($tac);
            $em->flush();

            return $tac;
        }
    }

    /**
     * Deletes attachments related to a ticket.
     *
     * Note: This actually just marks the blobs as is_temp, so they are cleaned up
     * as part of usual cleanup routines. E.g., the cleanup routine
     * will do the necessary work to delete the real file from wherever it is stored
     * (s3, filesystem, etc).
     *
     * @param int        $ticket_id
     * @param Connection $db
     */
    public static function deleteTicketAttachments($ticket_id, Connection $db)
    {
        $db->executeUpdate('
            UPDATE blobs
            LEFT JOIN tickets_attachments ON (tickets_attachments.blob_id = blobs.id)
            SET blobs.is_temp = 1
            WHERE tickets_attachments.ticket_id = ?
        ', [$ticket_id]);

        $db->executeUpdate('
            UPDATE blobs
            LEFT JOIN ticket_proc_log ON (ticket_proc_log.blob_id = blobs.id)
            SET blobs.is_temp = 1
            WHERE ticket_proc_log.ticket_id = ?
        ', [$ticket_id]);

        $db->executeUpdate(<<<'SQL'
            UPDATE blobs b
            LEFT JOIN custom_data_ticket cd ON (cd.value = b.id)
            LEFT JOIN custom_def_ticket cf ON (cd.root_field_id = cf.id)
            SET b.is_temp = 1
            WHERE cf.handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\File' AND cd.ticket_id = ?;
SQL
        , [$ticket_id]);

        $db->executeUpdate('
            UPDATE blobs
            LEFT JOIN email_sources ON (email_sources.blob_id = blobs.id)
            LEFT JOIN tickets_messages ON (tickets_messages.email_source_id = email_sources.id)
            SET blobs.is_temp = 1
            WHERE tickets_messages.ticket_id = ?
        ', [$ticket_id]);

        $db->executeUpdate('
            DELETE email_sources FROM email_sources
            INNER JOIN tickets_messages ON (tickets_messages.email_source_id = email_sources.id)
            WHERE tickets_messages.ticket_id = ?
        ', [$ticket_id]);
    }

    /**
     * @param Person             $person
     * @param EntityManager      $em
     * @param DeskproBlobStorage $blobStorage
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public static function deletePersonCallRecords(Person $person, EntityManager $em, DeskproBlobStorage $blobStorage)
    {
        $tickets = $em->getRepository(Ticket::class)->findBy(['person' => $person]);
        foreach ($tickets as $ticket) {
            self::deleteTicketsCallRecords($ticket, $em, $blobStorage);
        }
    }

    /**
     * @param Ticket             $ticket
     * @param EntityManager      $em
     * @param DeskproBlobStorage $blobStorage
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public static function deleteTicketsCallRecords(
        Ticket $ticket,
        EntityManager $em,
        DeskproBlobStorage $blobStorage
    ) {
        foreach ($ticket->getMessages() as $message) {
            if ($ticketMessageVoicePhoneCall = $message->getAttribute('voice_phone_call')) {
                /** @var TicketMessageVoicePhoneCall $ticketMessageVoicePhoneCall */
                $voicePhoneCall = $ticketMessageVoicePhoneCall->getPhoneCall();
                /** @var VoicePhoneCall $voicePhoneCall */
                $recording = $voicePhoneCall->getRecording();
                if ($recording) {
                    $voicePhoneCall->setRecording(null);
                    $em->persist($voicePhoneCall);

                    $ticketLog = new TicketLog();
                    $ticketLog
                        ->setTicket($ticket)
                        ->setIdObject($voicePhoneCall->getId())
                        ->setActionType(VoicePhoneCallLog::ACTION_RECORDING_DELETED)
                        ->setDetailItem('filesize', $recording->getFilesize() / 1024);
                    $em->persist($ticketLog);

                    $callLog = new VoicePhoneCallLog();
                    $callLog
                        ->setPhoneCall($voicePhoneCall)
                        ->setActionType(VoicePhoneCallLog::ACTION_RECORDING_DELETED);
                    $em->persist($callLog);

                    $blobStorage->deleteBlobRecord($recording);

                    $serializedData = App::getContainer()->get('serializer')->toArray(
                        new ApiWrapper($voicePhoneCall),
                        new SideloadSerializationContext()
                    );

                    App::getContainer()->get('event_dispatcher')->dispatch(
                        LegacySystemEvent::EVENT_NAME,
                        new LegacySystemEvent(
                            'agent.voice.recording_status',
                            ['data' => $serializedData]
                        )
                    );

                    $em->flush();
                }
            }
        }
    }
}
