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
use Application\ImportBundle\Generator\Exporter\Parser\NoColumnException;
use Orb\Util\Strings;

/**
 * DeskPRO tickets parser
 */
class Tickets extends AbstractParser
{
    /**
     * @var int
     */
    private $tickets_min_id;

    public function setTicketsMinId($minId)
    {
        $this->tickets_min_id = (int) $minId;
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
        return $this->tickets_min_id ? : $this->getBatchConfig()->getTicketsMinId();
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

            $batch = $this->reader->findTickets($this->getReaderBatchSize(), $this->getCurrentTicketsMinId());

            foreach ($batch as $num => $ticket) {
                $this->advanceProgressBar();

                $entity = $this->exportTicket($ticket);
                $collection->attach($entity);
                $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
                $this->tickets_min_id = $ticket['id'];
            }

            $this->entities_loaded += count($batch);

        } while ($batch->count());

        return $collection;
    }

    /**
     * @param DeskPROEntity\Ticket $ticket
     * @return Entity\Ticket
     */
    private function exportTicket(DeskPROEntity\Ticket $ticket)
    {

        $entity = new Entity\Ticket();

        $entity
            ->setDestination('ticket_' . $ticket['id'])
            ->setOid($ticket['id'])
            ->setRef($ticket['id'])

            ->setDepartment($ticket->department['title'])
            ->setPersonEmail($ticket->person ? $ticket->person->getPrimaryEmail()->email : null)
            ->setAgentEmail($ticket->agent ? $ticket->agent->getPrimaryEmail()->email : null)
            ->setAgentTeam($ticket->agent_team['name'])
            ->setOrganization($ticket->organization ? $ticket->organization['name'] : null)

            ->setStatus($ticket['status'])
            ->setDateCreated($ticket['date_created'])
            ->setDateResolved($ticket['date_resolved'])
            ->setDateArchived($ticket['date_archived'])
            ->setSubject($ticket['subject'])
            ->setLanguage($ticket->language ? $ticket->language['title'] : null)
            ->setAsHold($ticket['is_hold'])
            ->setUrgency($ticket['urgency'])

            ->setCategory($ticket->category ? $ticket->category['title'] : null)
            ->setWorkflow($ticket->workflow ? $ticket->workflow['title'] : null)
            ->setProduct($ticket->product ? $ticket->product['title'] : null)
        ;

        if ($priority = $ticket->priority) {
            $tp = new Entity\TicketPriority();
            $tp
                ->setTitle($priority['title'])
                ->setValue($priority['priority'])
                ->setDestination('priority')
            ;
            $entity->setPriority($tp);
        }

        foreach ($ticket->messages as $num => $message) {
            /** @var Entity\TicketMessage $message */
            $entity->addMessage($this->exportMessage($num, $message));
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
     * @param $num
     * @param DeskPROEntity\TicketMessage $message
     * @return Entity\TicketMessage
     */
    private function exportMessage($num, DeskPROEntity\TicketMessage $message)
    {
        $entity = new Entity\TicketMessage();
        $entity
            ->setDestination('message_' . $num)
            ->setOid($num)
            ->setPersonEmail($message->person->getPrimaryEmail()->email)
            ->setDateCreated($message['date_created'])
            ->setMessageHtml($message['message']);

        foreach ($message->attachments as $num => $attachment) {
            /** @var Entity\Attachment $attachment */
            $entity->addAttachment($this->exportAttachment($num, $attachment));
        }

        return $entity;
    }

    /**
     * @param $num
     * @param DeskPROEntity\TicketAttachment $attachment
     * @return Entity\Attachment
     */
    private function exportAttachment($num, DeskPROEntity\TicketAttachment $attachment)
    {
        $entity = new Entity\Attachment();
        $blob = $attachment->blob;
        $entity
            ->setDestination('attachment_' . $num)
            ->setOid($num)
            ->setBlobData(base64_encode($this->reader->getBlobData($blob)))
            ->setFileName($blob['filename'])
            ->setContentType($blob['content_type'])
        ;

        return $entity;
    }
}
