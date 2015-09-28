<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace Application\ImportBundle\Generator\Exporter\Parser\Csv;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;
use Application\ImportBundle\Reader\Csv\CsvReaderInterface;
use Orb\Util\Strings;

/**
 * Tickets csv file parser.
 *
 * Class Tickets
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
        return $this->getReaderCount(CsvReaderInterface::FILE_TICKETS);
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($this->getReaderData(CsvReaderInterface::FILE_TICKETS))
            ->setPrefix('CSVTicket')
            ->setRefColumn('id')
            ->setMethod('exportTicket')
            ->setAdvanceProgressbar(true)
        ;

        $collection    = $this->exportCollection($config);
        $messages      = $this->exportMessages();
        $custom_fields = $this->exportTicketCustomFields();

        foreach ($collection as $ticket) {
            /* @var Entity\Ticket $ticket */
            foreach ($messages as $message_entity) {
                if ($ticket->getDestination() === $message_entity->getDestination()) {
                    $ticket->addMessage($message_entity);
                }
            }
            foreach ($custom_fields as $custom_field_entity) {
                if ($ticket->getDestination() === $custom_field_entity->getDestination()) {
                    $ticket->addCustomField($custom_field_entity);
                }
            }

            $inline_custom_fields = $this->getInlineCustomFieldsParser()->export($ticket->getDestination(), $ticket->getRawData());
            foreach ($inline_custom_fields as $custom_field_entity) {
                $ticket->addCustomField($custom_field_entity);
            }
        }

        return $collection;
    }

    /**
     * Returns a ticket entity.
     *
     * @param array $data
     * @param int   $num
     *
     * @return Entity\Ticket
     */
    protected function exportTicket(array $data, $num)
    {
        $formatted = $this->formatter->format($data, array(
            'id'          => TransformerConfiguration::create(TransformerInterface::TYPE_STRING, array(
                'default' => 'num_'.$num,
            )),
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix'  => self::TICKET_PREFIX,
                'ref'     => 'id',
            )),
            'subject'      => TransformerInterface::TYPE_STRING,
            'user'         => TransformerInterface::TYPE_STRING,
            'agent'        => TransformerInterface::TYPE_STRING,
            'status'       => TransformerInterface::TYPE_STRING,
            'date_created' => TransformerInterface::TYPE_DATE,
        ));

        $entity = new Entity\Ticket();
        $entity
            ->setRawData($data)
            ->setDestination($formatted['destination'])
            ->setOid($formatted['id'])
            ->setRef(Strings::random(10, Strings::CHARS_ALPHANUM_IU))
            ->setSubject($formatted['subject'])
            ->setPersonEmail($formatted['user'])
            ->setAgentEmail($formatted['agent'])
            ->setStatus($formatted['status'] ?: DeskPROEntity\Ticket::STATUS_AWAITING_AGENT)
            ->setDateCreated($formatted['date_created'])
        ;

        // Set import key if it's real oid only
        if (strpos($entity->getOid(), 'num_') !== 0) {
            $entity->setImportMapKey(DeskPROEntity\ImportMap::TYPE_CSV_TICKET);
        }

        return $entity;
    }

    /**
     * Returns a collection of ticket messages.
     *
     * @return Entity\TicketMessage[]|Entity\Collection
     */
    protected function exportMessages()
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($this->getReaderData(CsvReaderInterface::FILE_TICKET_MESSAGES))
            ->setPrefix('CSVTicketMessage')
            ->setRefColumn('message_id')
            ->setMethod('exportMessage')
        ;

        $collection  = $this->exportCollection($config);
        $attachments = $this->exportTicketAttachments();

        foreach ($collection as $message) {
            foreach ($attachments as $attachment) {
                if ($attachment->getDestination() === self::MESSAGE_PREFIX.$message->getOid()) {
                    $message->addAttachment($attachment);
                }
            }
        }

        return $collection;
    }

    /**
     * Returns a ticket message.
     *
     * @param array $data
     * @param int   $num
     *
     * @return Entity\TicketMessage
     */
    protected function exportMessage(array $data, $num)
    {
        $formatted = $this->formatter->format($data, array(
            'ticket_id'   => TransformerInterface::TYPE_STRING,
            'message_id'  => TransformerConfiguration::create(TransformerInterface::TYPE_STRING, array(
                'default' => 'num_'.$num,
            )),
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix'  => self::TICKET_PREFIX,
                'ref'     => 'ticket_id',
            )),
            'message_text' => TransformerInterface::TYPE_STRING,
            'user'         => TransformerInterface::TYPE_STRING,
            'date_created' => TransformerInterface::TYPE_DATE,
        ));

        $entity = new Entity\TicketMessage();
        $entity
            ->setRawData($data)
            ->setDestination($formatted['destination'])
            ->setOid($formatted['message_id'])
            ->setPersonEmail($formatted['user'])
            ->setMessageText($formatted['message_text'])
            ->setDateCreated($formatted['date_created'])
        ;

        return $entity;
    }

    /**
     * Returns a collection of ticket attachments.
     *
     * @return Entity\Attachment[]|Entity\Collection
     */
    private function exportTicketAttachments()
    {
        $data = $this->getReaderData(CsvReaderInterface::FILE_TICKET_ATTACHMENTS);

        return $this->getAttachmentParser()->exportAttachments($data, self::MESSAGE_PREFIX, 'message_id');
    }

    /**
     * Returns a collection of ticket custom field data.
     *
     * @return Entity\CustomField[]|Entity\Collection
     */
    private function exportTicketCustomFields()
    {
        $data = $this->getReaderData(CsvReaderInterface::FILE_TICKET_CUSTOM_FIELDS);

        return $this->getMultipleCustomFieldsParser()->export($data, self::TICKET_PREFIX, 'ticket_id');
    }
}
