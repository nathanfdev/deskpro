<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\Generator\Exporter\Parser\DeskPRO;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;
use Application\ImportBundle\Generator\Exporter\Parser\ParserPeopleStorageInterface;
use Application\ImportBundle\Reader\DeskPRO\DeskPROReaderInterface;
use Orb\Util\Strings;

/**
 * DeskPRO tickets parser
 *
 * Class Tickets
 * @package Application\ImportBundle\Generator\Exporter\Parser\DeskPRO
 */
final class Tickets extends AbstractParser
{
    /**
     * @var int
     */
    private $tickets_min_id = 0;

    /**
     * @var ParserPeopleStorageInterface
     */
    private $people_storage;

    /**
     * Constructor
     *
     * @param DeskPROReaderInterface       $reader
     * @param ParserPeopleStorageInterface $people_storage
     * @param int                          $min_id
     */
    public function __construct(DeskPROReaderInterface $reader, ParserPeopleStorageInterface $people_storage, $min_id = 0)
    {
        parent::__construct($reader);

        $this->people_storage = $people_storage;
        $this->tickets_min_id = (int)$min_id;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_TICKET;
    }

    /**
     * Returns current tickets offset
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
        return $this->reader->getTicketsCount($this->getCurrentTicketsMinId());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $this->entities_loaded = 0;
        $collection = new Entity\Collection();

        do {
            $batch  = $this->reader->findTickets($this->getReaderBatchSize(), $this->getCurrentTicketsMinId());
            $config = new ExportCollectionConfig();
            $config
                ->setData($batch)
                ->setPrefix('DPTicket')
                ->setRefColumn('id')
                ->setMethod('exportTicket')
                ->setAdvanceProgressbar(true)
            ;

            $collection->merge($this->exportCollection($config));

            $this->entities_loaded += count($batch);
            $this->tickets_min_id   = max($this->tickets_min_id, $collection->getMaxOid());

        } while (count($batch) > 0);

        return $collection;
    }

    /**
     * @param DeskPROEntity\Ticket $ticket
     * @return Entity\Ticket
     */
    protected function exportTicket(DeskPROEntity\Ticket $ticket)
    {
        $entity = new Entity\Ticket();
        $entity
            ->setRawData($ticket->toArray())
            ->setDestination('ticket_' . $ticket->getId())
            ->setOid($ticket->getId())
            ->setRef($ticket->getRef())

            ->setDepartment($ticket->department ? $ticket->department->getRealTitle() : null)
            ->setPersonEmail($ticket->person ? $ticket->person->getPrimaryEmail()->email : null)
            ->setAgentEmail($ticket->agent ? $ticket->agent->getPrimaryEmail()->email : null)
            ->setAgentTeam($ticket->agent_team['name'])
            ->setOrganization($ticket->organization ? $ticket->organization['name'] : null)

            ->setStatus($ticket->getStatusCode())
            ->setDateCreated($ticket['date_created'])
            ->setDateResolved($ticket['date_resolved'])
            ->setDateArchived($ticket['date_archived'])
            ->setSubject($ticket->getSubject())
            ->setLanguage($ticket->language ? $ticket->language['title'] : null)
            ->setAsHold($ticket['is_hold'])
            ->setUrgency($ticket['urgency'])

            ->setCategory($ticket->category ? $ticket->category->getRealTitle() : null)
            ->setWorkflow($ticket->workflow ? $ticket->workflow->getRealTitle() : null)
            ->setProduct($ticket->product ? $ticket->product->getRealTitle() : null)
        ;

        $priority = $ticket->priority;
        if ($priority) {
            $ticket_priority = new Entity\TicketPriority();
            $ticket_priority
                ->setOid($priority->getId())
                ->setDestination('priority_' . $priority->getId())
                ->setTitle($priority->getRealTitle())
                ->setValue($priority['priority'])
            ;

            $entity->setPriority($ticket_priority);
        }

        foreach ($ticket->messages as $num => $message) {
            $entity->addMessage($this->exportMessage($message));
        }
        foreach ($ticket->labels as $label) {
            $entity->addLabel($label['label']);
        }
        foreach ($ticket->participants as $participant) {
            /** @var $participant DeskPROEntity\Person */
            $entity->addParticipant($participant->getPrimaryEmail()->email);
        }

        // todo custom fields


        return $entity;
    }

    /**
     * @param DeskPROEntity\TicketMessage $message
     * @return Entity\TicketMessage
     */
    private function exportMessage(DeskPROEntity\TicketMessage $message)
    {
        $entity = new Entity\TicketMessage();
        $entity
            ->setRawData($message->toArray())
            ->setDestination('message_' . $message->getId())
            ->setOid($message->getId())
            ->setPersonEmail($message->person->getPrimaryEmail()->email)
            ->setDateCreated($message['date_created'])
            ->setMessageHtml($message['message'])
            ->setAsNote((bool)$message['is_agent_note'])
        ;

        foreach ($message->attachments as $num => $attachment) {
            $entity->addAttachment($this->exportAttachment($attachment));
        }

        return $entity;
    }

    /**
     * @param DeskPROEntity\TicketAttachment $attachment
     * @return Entity\Attachment
     */
    private function exportAttachment(DeskPROEntity\TicketAttachment $attachment)
    {
        $blob   = $attachment->getBlob();
        $entity = new Entity\Attachment();
        $entity
            ->setDestination('attachment_' . $attachment->getId())
            ->setOid($attachment->getId())
            ->setBlobData(base64_encode($this->reader->getBlobData($blob)))
            ->setFileName($blob['filename'])
            ->setContentType($blob['content_type'])
        ;

        return $entity;
    }
}
