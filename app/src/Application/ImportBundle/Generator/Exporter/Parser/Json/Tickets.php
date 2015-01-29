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

namespace Application\ImportBundle\Generator\Exporter\Parser\Json;

use Application\ImportBundle\Generator\GeneratorInterface;
use Application\ImportBundle\Generator\Writer\Json\Destination;
use Application\ImportBundle\Entity;
use DateTime;

/**
 * Tickets json file parser
 *
 * Class Tickets
 * @package Application\ImportBundle\Generator\Exporter\Parser\Json
 */
class Tickets extends AbstractParser
{
    /**
     * {@inheritdoc}
     */
    public function getRecordType()
    {
        return GeneratorInterface::RECORD_TYPE_TICKET;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return $this->reader->getDirectoryFilesCount($this->getConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection = new Entity\Collection();
        $tickets = $this->reader->getData($this->getConfig());
        foreach ($tickets as $num => $ticket) {
            $this->advanceProgressBar();

            if ($this->hasRequiredTicketColumns($ticket) === false) {
                $this->logWarning(sprintf('Invalid ticket record found (Skipping): %d', $num));
            } else {
                $entity = new Entity\Ticket();
                $entity
                    ->setDestination('ticket_' . $ticket['ref'])
                    ->setRef($ticket['ref'])
                    ->setDepartment($ticket['department'])
                    ->setPersonEmail($ticket['person'])
                    ->setAgentEmail($ticket['agent'])
                    ->setAgentTeam($ticket['agent_team'])
                    ->setStatus($ticket['status'])
                    ->setDateCreated(new DateTime($ticket['date_created']))
                    ->setSubject($ticket['subject'])
                    ->setPriority($ticket['priority']);

                $messages = $this->exportMessages($ticket['messages']);
                foreach ($messages as $message) {
                    /** @var Entity\TicketMessage $message */
                    $entity->addMessage($message);
                }

                $collection->attach($entity);
                $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
            }
        }

        return $collection;
    }

    /**
     * Returns a collection of the ticket messages
     *
     * @param array $messages
     * @return Entity\Collection
     */
    private function exportMessages(array $messages)
    {
        $collection = new Entity\Collection();
        foreach ($messages as $num => $message) {
            if ($this->hasRequiredMessageColumns($message) === false) {
                $this->logWarning(sprintf('Invalid ticket message record found (Skipping): %d', $num));
            } else {
                $entity = new Entity\TicketMessage();
                $entity
                    ->setPersonEmail($message['person'])
                    ->setMessageText($message['message_text'])
                    ->setDateCreated(new DateTime($message['date_created']));

                $attachments = $this->exportAttachments($message['attachments']);
                foreach ($attachments as $attachment) {
                    /** @var Entity\TicketAttachment $attachment */
                    $entity->addAttachment($attachment);
                }

                $collection->attach($entity);
            }
        }

        return $collection;
    }

    /**
     * Returns a collection of the ticket message attachments
     *
     * @param array $attachments
     * @return Entity\Collection
     */
    private function exportAttachments(array $attachments)
    {
        $collection = new Entity\Collection();
        foreach ($attachments as $num => $attachment) {
            if ($this->hasRequiredAttachmentColumns($attachment) === false) {
                $this->logWarning(sprintf('Invalid ticket message attachment record found (Skipping): %d', $num));
            } else {
                $entity = new Entity\TicketAttachment();
                $entity
                    ->setOid($attachment['oid'])
                    ->setBlobData($attachment['blob_data'])
                    ->setFileName($attachment['file_name'])
                    ->setContentType($attachment['content_type']);

                $collection->attach($entity);
            }
        }

        return $collection;
    }

    /**
     * Returns record type reader config
     *
     * @return \Application\ImportBundle\Reader\Json\JsonConfig
     */
    private function getConfig()
    {
        return $this->getReaderConfig(Destination\DestinationInterface::ENTITY_TICKET_PATH);
    }

    /**
     * Check if ticket has all required columns
     *
     * @param array $ticket
     * @return bool
     */
    private function hasRequiredTicketColumns(array $ticket)
    {
        $columns = array(
            'ref',
            'department',
            'person',
            'agent',
            'agent_team',
            'status',
            'date_created',
            'subject',
            'priority',
            'messages',
        );

        return $this->hasRequiredColumns($ticket, $columns) && is_array($ticket['messages']);
    }

    /**
     * Check if ticket message has all required columns
     *
     * @param array $message
     * @return bool
     */
    private function hasRequiredMessageColumns(array $message)
    {
        $columns = array(
            'person',
            'date_created',
            'message_text',
            'attachments',
        );

        return $this->hasRequiredColumns($message, $columns) && is_array($message['attachments']);
    }

    /**
     * Check if ticket message attachment has all required columns
     *
     * @param array $attachment
     * @return bool
     */
    private function hasRequiredAttachmentColumns(array $attachment)
    {
        return $this->hasRequiredColumns($attachment, array(
            'oid',
            'blob_data',
            'file_name',
            'content_type',
        ));
    }
}
