<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace Application\ImportBundle\Generator\Exporter\Parser\OsTicket;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\FormatterInterface;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;
use Application\ImportBundle\Generator\Exporter\Parser\ParserPeopleStorageInterface;
use Application\ImportBundle\Reader\OsTicket\OsTicketReaderInterface;
use Orb\Util\Strings;

/**
 * OsTicket tickets parser.
 *
 * Class Tickets
 */
final class Tickets extends AbstractParser
{
    /**
     * @var int
     */
    private $tickets_min_id;

    /**
     * @var ParserPeopleStorageInterface
     */
    private $tickets_people;

    /**
     * Constructor.
     *
     * @param OsTicketReaderInterface      $reader
     * @param FormatterInterface           $formatter
     * @param ParserPeopleStorageInterface $tickets_people
     */
    public function __construct(OsTicketReaderInterface $reader, FormatterInterface $formatter, ParserPeopleStorageInterface $tickets_people)
    {
        parent::__construct($reader, $formatter);
        $this->tickets_people = $tickets_people;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_TICKET;
    }

    /**
     * Returns current tickets offset.
     *
     * @return int
     */
    public function getCurrentTicketsMinId()
    {
        return max($this->tickets_min_id, $this->getBatchConfig()->getTicketsMinId());
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return count($this->getTickets());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($this->getTickets())
            ->setPrefix('OSTicket')
            ->setRefColumn('ticket_id')
            ->setMethod('exportTicket')
            ->setAdvanceProgressbar(true)
        ;

        return $this->exportCollection($config);
    }

    /**
     * Returns a ticket entity.
     *
     * @param array $data
     *
     * @return Entity\Ticket|null
     */
    protected function exportTicket(array $data)
    {
        $formatted = $this->formatter->format($data, [
            'ticket_id'   => TransformerInterface::TYPE_INT,
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, [
                'prefix' => 'ticket_',
                'ref'    => 'ticket_id',
            ]),
            'number'      => TransformerInterface::TYPE_STRING,
            'dept_id'     => TransformerInterface::TYPE_INT,
            'user_id'     => TransformerInterface::TYPE_INT,
            'staff_id'    => TransformerInterface::TYPE_INT,
            'team_id'     => TransformerInterface::TYPE_INT,
            'created'     => TransformerInterface::TYPE_DATE,
            'subject'     => TransformerInterface::TYPE_STRING,
            'priority_id' => TransformerInterface::TYPE_INT,
            'isanswered'  => TransformerInterface::TYPE_BOOLEAN,
            'closed'      => TransformerInterface::TYPE_BOOLEAN,
            'messages'    => TransformerInterface::TYPE_ARRAY,
        ]);

        $entity = new Entity\Ticket();
        $entity
            ->setRawData($data)
            ->setDestination($formatted['destination'])
            ->setOid($formatted['ticket_id'])
            ->setRef(Strings::random(10, Strings::CHARS_ALPHANUM_IU))
            ->setDepartment($this->reader->findDepartmentById($formatted['dept_id']))
            ->setPersonEmail($this->reader->findUserEmailById($formatted['user_id']))
            ->setAgentEmail($this->reader->findStaffEmailById($formatted['staff_id']))
            ->setAgentTeam($this->reader->findTeamNameById($formatted['team_id']))
            ->setStatus($this->getTicketStatus($formatted))
            ->setDateCreated($formatted['created'])
            ->setSubject($formatted['subject'])
            ->setPriority($this->exportPriority($formatted['priority_id']))
        ;

        $messages = $this->exportMessages($formatted['messages']);
        foreach ($messages as $message) {
            $entity->addMessage($message);
        }

        return $entity;
    }

    /**
     * Returns a ticket priority entity.
     *
     * @param int $id
     *
     * @return Entity\TicketPriority|null
     */
    protected function exportPriority($id)
    {
        if ($id) {
            $data   = $this->reader->findTicketPriority($id);
            $entity = new Entity\TicketPriority();
            $entity
                ->setRawData($data)
                ->setDestination('priority')
                ->setOid($data['priority_id'])
                ->setTitle($data['priority_desc'])
                ->setValue($data['priority_urgency'])
            ;

            return $entity;
        }

        return;
    }

    /**
     * Returns a collection of the ticket messages.
     *
     * @param int $ticket_id
     *
     * @return Entity\TicketMessage[]|Entity\Collection
     */
    protected function exportMessages($ticket_id)
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($this->reader->findMessages($ticket_id))
            ->setPrefix('OSTicketMessage')
            ->setRefColumn('id')
            ->setMethod('exportMessage')
        ;

        return $this->exportCollection($config);
    }

    /**
     * Returns a ticket message entity.
     *
     * @param array $data
     *
     * @return Entity\TicketMessage|null
     */
    protected function exportMessage(array $data)
    {
        $formatted = $this->formatter->format($data, [
            'id'          => TransformerInterface::TYPE_INT,
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, [
                'prefix' => 'message_',
                'ref'    => 'id',
            ]),
            'thread_type' => TransformerInterface::TYPE_STRING,
            'staff_id'    => TransformerInterface::TYPE_INT,
            'user_id'     => TransformerInterface::TYPE_INT,
            'created'     => TransformerInterface::TYPE_DATE,
            'body'        => TransformerInterface::TYPE_STRING,
        ]);

        $entity = new Entity\TicketMessage();
        $entity
            ->setRawData($data)
            ->setDestination($formatted['destination'])
            ->setOid($formatted['id'])
            ->setPersonEmail($this->getMessagePersonEmail($formatted))
            ->setDateCreated($formatted['created'])
            ->setMessageHtml($formatted['body'])
        ;

        $attachments = $this->exportAttachments($formatted['id']);
        foreach ($attachments as $attachment) {
            $entity->addAttachment($attachment);
        }

        return $entity;
    }

    /**
     * Returns a collection of the ticket message attachments.
     *
     * @param int $message_id
     *
     * @return Entity\Attachment[]|Entity\Collection
     */
    protected function exportAttachments($message_id)
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($this->reader->findMessageAttachments($message_id))
            ->setPrefix('OSTicketAttachment')
            ->setRefColumn('file_id')
            ->setMethod('exportAttachment')
        ;

        return $this->exportCollection($config);
    }

    /**
     * Returns an attachment entity.
     *
     * @param array $data
     *
     * @return Entity\Attachment|null
     */
    protected function exportAttachment(array $data)
    {
        $formatted = $this->formatter->format($data, $configuration = [
            'file_id'     => TransformerInterface::TYPE_INT,
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, [
                'prefix' => 'attachment_',
                'ref'    => 'file_id',
            ]),
            'name' => TransformerInterface::TYPE_STRING,
            'type' => TransformerInterface::TYPE_STRING,
        ]);

        $entity = new Entity\Attachment();
        $entity
            ->setRawData($data)
            ->setDestination($formatted['destination'])
            ->setOid($formatted['file_id'])
            ->setBlobData(base64_encode($this->reader->findAttachmentData($formatted['file_id'])))
            ->setFileName($formatted['name'])
            ->setContentType($formatted['type'])
        ;

        return $entity;
    }

    /**
     * Get ticket status by raw data.
     *
     * @param array $ticket
     *
     * @return string
     */
    private function getTicketStatus(array $ticket)
    {
        $status = DeskPROEntity\Ticket::STATUS_AWAITING_AGENT;
        if ($ticket['isanswered']) {
            $status = DeskPROEntity\Ticket::STATUS_AWAITING_USER;
        }
        if ($ticket['closed']) {
            $status = DeskPROEntity\Ticket::STATUS_RESOLVED;
        }

        return $status;
    }

    /**
     * Get ticket message person email.
     *
     * @param array $message
     *
     * @return null|string
     */
    private function getMessagePersonEmail(array $message)
    {
        $email = null;
        if ($message['thread_type'] === 'R' && $message['staff_id']) {
            $email = $this->reader->findStaffEmailById($message['staff_id']);
        } elseif ($message['thread_type'] === 'M' && $message['user_id']) {
            $email = $this->reader->findUserEmailById($message['user_id']);
        }

        return $email;
    }

    /**
     * Returns tickets
     * Loads from osTicket database.
     *
     * @return array
     */
    private function getTickets()
    {
        $this->entities_loaded = 0;

        $tickets = [];
        $min_id  = $this->getBatchConfig()->getTicketsMinId();

        do {
            $batch = $this->reader->findTickets($this->getReaderBatchSize(), $min_id);
            $this->entities_loaded += count($batch);

            foreach ($batch as &$ticket) {
                if (isset($ticket['ticket_id'])) {
                    $ticket['messages'] = $this->reader->findMessages($ticket['ticket_id']);
                    $min_id             = max($min_id, $ticket['ticket_id']);
                }
            }

            $tickets = array_merge($tickets, $batch);
        } while (count($batch) > 0);

        $this->tickets_people->loadBy($tickets);
        $this->tickets_min_id = $min_id;

        return $tickets;
    }
}
