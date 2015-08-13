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

namespace Application\ImportBundle\Generator\Exporter\Parser\OsTicket;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Orb\Util\Strings;

/**
 * OsTicket tickets parser
 *
 * Class Tickets
 * @package Application\ImportBundle\Generator\Exporter\Parser\OsTicket
 */
final class Tickets extends AbstractParser
{
    /**
     * @var int
     */
    private $tickets_min_id;

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

        while ($batch = $this->reader->findTickets($this->getReaderBatchSize(), $this->getCurrentTicketsMinId())) {
            foreach ($batch as $num => $ticket) {
                $this->advanceProgressBar();
                $offsetNum = $num + $this->entities_loaded;

                try {
                    $entity = $this->exportTicket($ticket);
                    if ($entity) {
                        $collection->attach($entity);
                        $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
                    } else {
                        $this->logWarning(sprintf('Invalid ticket record found (Skipping): %d', $offsetNum));
                    }

                } catch (\Exception $e) {
                    $this->logWarning(sprintf(
                        'Invalid ticket record `%d` found (Skipping): %s',
                        $num, $e->getMessage()
                    ));
                }

                if (isset($ticket['ticket_id'])) {
                    $this->tickets_min_id = $ticket['ticket_id'];
                }
            }

            $this->entities_loaded += count($batch);
        }

        return $collection;
    }

    /**
     * Returns a ticket entity
     *
     * @param array $data
     * @return Entity\Ticket|null
     */
    private function exportTicket(array $data)
    {
        $formatted = $this->formatter->format($data, array(
            'ticket_id'   => TransformerInterface::TYPE_INT,
            'destination'     => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix' => 'ticket_',
                'ref'    => 'ticket_id',
            )),
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
        ));

        $entity = new Entity\Ticket();
        $entity
            ->setRawData($data)
            ->setDestination($formatted['destination'])
            ->setOid($formatted['ticket_id'])
            ->setRef(Strings::random(10, Strings::CHARS_ALPHANUM_IU))
            ->setDepartment($this->reader->findDepartmentById($formatted['dept_id']))
            ->setPersonEmail($this->reader->findUserEmailById($formatted['user_id']))
            ->setAgentEmail($this->reader->findUserEmailById($formatted['staff_id']))
            ->setAgentTeam($this->reader->findUserEmailById($formatted['team_id']))
            ->setStatus($this->getTicketStatus($formatted))
            ->setDateCreated($formatted['created'])
            ->setSubject($formatted['subject'])
            ->setPriority($this->exportPriority($formatted['priority_id']))
        ;

        $messages = $this->exportMessages($formatted['ticket_id']);
        foreach ($messages as $message) {
            /** @var Entity\TicketMessage $message */
            $entity->addMessage($message);
        }

        return $entity;
    }

    /**
     * Returns a ticket priority entity
     *
     * @param int $id
     * @return Entity\TicketPriority|null
     */
    private function exportPriority($id)
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

        return null;
    }

    /**
     * Returns a collection of the ticket messages
     *
     * @param int $ticket_id
     * @return Entity\Collection
     */
    private function exportMessages($ticket_id)
    {
        $messages   = $this->reader->findMessages($ticket_id);
        $collection = new Entity\Collection();

        foreach ($messages as $num => $message) {
            try {
                $entity = $this->exportMessage($message);
                if ($entity) {
                    $collection->attach($entity);
                    $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
                } else {
                    $this->logWarning(sprintf('Invalid ticket message record found (Skipping): %d', $num));
                }

            } catch (\Exception $e) {
                $this->logWarning(sprintf(
                    'Invalid ticket message record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));
            }
        }

        return $collection;
    }

    /**
     * Returns a ticket message entity
     *
     * @param array $data
     * @return Entity\TicketMessage|null
     */
    private function exportMessage(array $data)
    {
        $formatted = $this->formatter->format($data, array(
            'id'          => TransformerInterface::TYPE_INT,
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix' => 'message_',
                'ref'    => 'id',
            )),
            'thread_type' => TransformerInterface::TYPE_STRING,
            'staff_id'    => TransformerInterface::TYPE_INT,
            'user_id'     => TransformerInterface::TYPE_INT,
            'created'     => TransformerInterface::TYPE_DATE,
            'body'        => TransformerInterface::TYPE_STRING,
        ));

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
            /** @var Entity\Attachment $attachment */
            $entity->addAttachment($attachment);
        }

        return $entity;
    }

    /**
     * Returns a collection of the ticket message attachments
     *
     * @param int $message_id
     * @return Entity\Collection
     */
    private function exportAttachments($message_id)
    {
        $collection  = new Entity\Collection();
        $attachments = $this->reader->findMessageAttachments($message_id);

        foreach ($attachments as $num => $attachment) {
            try {
                $entity = $this->exportAttachment($attachment);
                if ($entity) {
                    $collection->attach($entity);
                    $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
                } else {
                    $this->logWarning(sprintf('Invalid ticket message attachment record found (Skipping): %d', $num));
                }

            } catch (\Exception $e) {
                $this->logError(sprintf(
                    'Invalid ticket message attachment record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));
            }
        }

        return $collection;
    }

    /**
     * Returns an attachment entity
     *
     * @param array $data
     * @return Entity\Attachment|null
     */
    private function exportAttachment(array $data)
    {
        $formatted = $this->formatter->format($data, $configuration = array(
            'file_id'     => TransformerInterface::TYPE_INT,
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix' => 'attachment_',
                'ref'    => 'file_id',
            )),
            'name'        => TransformerInterface::TYPE_STRING,
            'type'        => TransformerInterface::TYPE_STRING,
        ));

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
     * Get ticket status by raw data
     *
     * @param array $ticket
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
     * Get ticket message person email
     *
     * @param array $message
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
}
