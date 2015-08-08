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
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerException;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
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
                $entity = $this->exportTicket($num, $ticket);

                foreach ($messages as $message_entity) {
                    /** @var Entity\TicketMessage $message_entity */
                    if ($entity->getDestination() === $message_entity->getDestination()) {
                        $entity->addMessage($message_entity);
                    }
                }
                foreach ($custom_fields as $custom_field_entity) {
                    /** @var Entity\CustomField $custom_field_entity */
                    if ($entity->getDestination() === $custom_field_entity->getDestination()) {
                        $entity->addCustomField($custom_field_entity);
                    }
                }

                $inline_custom_fields = $this->exportInlineCustomFields($entity->getDestination(), $ticket);
                foreach ($inline_custom_fields as $custom_field_entity) {
                    $entity->addCustomField($custom_field_entity);
                }

                $collection->attach($entity);
                $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));

            } catch (TransformerException $e) {
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
     * @param int   $num
     * @param array $data
     *
     * @return Entity\Ticket|null
     */
    private function exportTicket($num, array $data)
    {
        $configuration = array(
            'id' => TransformerConfiguration::create(TransformerInterface::TYPE_STRING, array(
                'default' => 'num_' . $num,
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
        );

        $formatted = $this->formatter->format($data, $configuration);
        $entity = new Entity\Ticket();
        $entity
            ->setRawData($data)
            ->setDestination($formatted['destination'])
            ->setOid($formatted['id'])
            ->setRef(Strings::random(10, Strings::CHARS_ALPHANUM_IU))
            ->setSubject($formatted['subject'])
            ->setPersonEmail($formatted['user'])
            ->setAgentEmail($formatted['agent'])
            ->setStatus($formatted['status'] ? : DeskPROEntity\Ticket::STATUS_AWAITING_AGENT)
            ->setDateCreated($formatted['date_created'])
        ;

        return $entity;
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
                $entity = $this->exportMessage($num, $message);

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

            } catch (TransformerException $e) {
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
     * @param int   $num
     * @param array $data
     *
     * @return Entity\TicketMessage|null
     */
    private function exportMessage($num, array $data)
    {
        $configuration = array(
            'ticket_id'  => TransformerInterface::TYPE_STRING,
            'message_id' => TransformerConfiguration::create(TransformerInterface::TYPE_STRING, array(
                'default' => 'num_' . $num,
            )),
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix'  => self::TICKET_PREFIX,
                'ref'     => 'ticket_id',
            )),
            'message_text' => TransformerInterface::TYPE_STRING,
            'user'         => TransformerInterface::TYPE_STRING,
            'date_created' => TransformerInterface::TYPE_DATE,
        );

        $formatted = $this->formatter->format($data, $configuration);
        $entity    = new Entity\TicketMessage();
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
     * Returns a collection of ticket attachments
     *
     * @return Entity\Collection
     */
    private function exportTicketAttachments()
    {
        $config = $this->getReaderConfig(self::FILE_TICKET_ATTACHMENTS);
        return $this->exportAttachments($config, self::MESSAGE_PREFIX, 'message_id');
    }

    /**
     * Returns a collection of ticket custom field data
     *
     * @return Entity\Collection
     */
    private function exportTicketCustomFields()
    {
        $config = $this->getReaderConfig(self::FILE_TICKET_CUSTOM_FIELDS);
        return $this->exportCustomFields($config, self::TICKET_PREFIX, 'ticket_id');
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
}
