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

namespace Application\ImportBundle\Generator\Exporter\Parser\Csv;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Parser\NoColumnException;
use Orb\Util\Strings;

/**
 * Tickets csv file parser
 *
 * Class Tickets
 * @package Application\ImportBundle\Generator\Exporter\Parser\Csv
 */
final class Tickets extends AbstractParser
{
    const TICKET_PREFIX  = 'ticket_';
    const MESSAGE_PREFIX = 'message_';

    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_TICKET;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return $this->getReaderCount($this->getTicketReaderConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection    = new Entity\Collection();

        $tickets       = $this->getReaderData($this->getTicketReaderConfig());
        $messages      = $this->exportMessages();
        $custom_fields = $this->exportTicketCustomFields();

        foreach ($tickets as $num => $ticket) {
            $this->advanceProgressBar();

            try {
                $entity = $this->exportTicket($ticket);
                if ($entity) {
                    foreach ($messages as $message_entity) {
                        /** @var Entity\TicketMessage $message_entity */
                        if ($entity->getDestination() === $message_entity->getDestination()) {
                            $entity->addMessage($message_entity);
                        }
                    }
                    foreach ($custom_fields as $custom_field_entity) {
                        /** @var Entity\TicketMessage $message_entity */
                        if ($entity->getDestination() === $custom_field_entity->getDestination()) {
                            $entity->addCustomField($custom_field_entity);
                        }
                    }

                    $collection->attach($entity);
                    $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
                } else {
                    $this->logWarning(sprintf('Invalid ticket record `%d` found (Skipping)', $num));
                }

            } catch (NoColumnException $e) {
                $this->logWarning(sprintf(
                    'Invalid ticket record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));
            }
        }

        return $collection;
    }

    /**
     * Returns a ticket entity
     *
     * @param array $ticket
     * @return Entity\Ticket|null
     */
    private function exportTicket(array $ticket)
    {
        if ($this->isTicketValid($ticket)) {
            $entity = new Entity\Ticket();
            $entity
                ->setDestination(self::TICKET_PREFIX . $ticket['id'])
                ->setOid($ticket['id'])
                ->setRef(Strings::random(10, Strings::CHARS_ALPHANUM_IU))
                ->setSubject($ticket['subject'])
                ->setPersonEmail($ticket['user'])
                ->setAgentEmail($ticket['agent'])
                ->setStatus($ticket['status'] ?: DeskPROEntity\Ticket::STATUS_AWAITING_AGENT)
                ->setDateCreated($this->getFromStringOrCurrentDateTime($ticket['date_created']));

            return $entity;
        }

        return null;
    }

    /**
     * Returns a collection of ticket messages
     *
     * @return Entity\Collection
     */
    private function exportMessages()
    {
        $collection  = new Entity\Collection();
        $messages    = $this->getReaderData($this->getTicketMessageReaderConfig());
        $attachments = $this->exportTicketAttachments();

        foreach ($messages as $num => $message) {
            try {
                $entity = $this->exportMessage($message);
                if ($entity) {
                    foreach ($attachments as $attachment) {
                        /** @var Entity\Attachment $attachment */
                        if ($attachment->getDestination() === self::MESSAGE_PREFIX . $entity->getOid()) {
                            $entity->addAttachment($attachment);
                        }
                    }

                    $collection->attach($entity);
                    $this->logInfo(sprintf(
                        'Entity `%s%s` parsed successfully!',
                        self::MESSAGE_PREFIX,  $entity->getOid()
                    ));
                } else {
                    $this->logWarning(sprintf('Invalid ticket message record `%d` found (Skipping)', $num));
                }

            } catch (NoColumnException $e) {
                $this->logWarning(sprintf(
                    'Invalid ticket message record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));
            }
        }

        return $collection;
    }

    /**
     * Returns a ticket message
     *
     * @param array $message
     * @return Entity\TicketMessage|null
     */
    private function exportMessage(array $message)
    {
        if ($this->isMessageValid($message)) {
            $entity = new Entity\TicketMessage();
            $entity
                ->setDestination(self::TICKET_PREFIX . $message['ticket_id'])
                ->setOid($message['message_id'])
                ->setPersonEmail($message['user'])
                ->setMessageText($message['message_text'])
                ->setDateCreated($this->getFromStringOrCurrentDateTime($message['date_created']));

            return $entity;
        }

        return null;
    }

    /**
     * Returns a collection of ticket attachments
     *
     * @return Entity\Collection
     */
    private function exportTicketAttachments()
    {
        return $this->exportAttachments($this->getTicketAttachmentReaderConfig(), self::MESSAGE_PREFIX, 'message_id');
    }

    /**
     * Returns a collection of ticket custom field data
     *
     * @return Entity\Collection
     */
    private function exportTicketCustomFields()
    {
        return $this->exportCustomFields($this->getTicketCustomFieldReaderConfig(), self::CUSTOM_FIELD_PREFIX, 'ticket_id');
    }

    /**
     * Check if ticket has all required columns
     *
     * @param array $ticket
     * @return bool
     */
    private function isTicketValid(array $ticket)
    {
        $columns = array(
            'id',
            'subject',
            'user',
            'agent',
            'status',
            'date_created',
        );

        return $this->hasRequiredColumns($ticket, $columns);
    }

    /**
     * Check if ticket message has all required columns
     *
     * @param array $message
     * @return bool
     */
    private function isMessageValid(array $message)
    {
        $columns = array(
            'ticket_id',
            'message_id',
            'message_text',
            'user',
        );

        return $this->hasRequiredColumns($message, $columns);
    }

    /**
     * Returns reader config for ticket records
     *
     * @return \Application\ImportBundle\Reader\Csv\CsvConfig
     */
    private function getTicketReaderConfig()
    {
        return $this->getReaderConfig(self::FILE_TICKETS);
    }

    /**
     * Returns reader config for ticket message records
     *
     * @return \Application\ImportBundle\Reader\Csv\CsvConfig
     */
    private function getTicketMessageReaderConfig()
    {
        return $this->getReaderConfig(self::FILE_TICKET_MESSAGES);
    }

    /**
     * Returns reader config for ticket attachment records
     *
     * @return \Application\ImportBundle\Reader\Csv\CsvConfig
     */
    private function getTicketAttachmentReaderConfig()
    {
        return $this->getReaderConfig(self::FILE_TICKET_ATTACHMENTS);
    }

    /**
     * Returns reader config for ticket custom field records
     *
     * @return \Application\ImportBundle\Reader\Csv\CsvConfig
     */
    private function getTicketCustomFieldReaderConfig()
    {
        return $this->getReaderConfig(self::FILE_TICKET_CUSTOM_FIELDS);
    }
}
