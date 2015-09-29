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

namespace Application\ImportBundle\Generator\Exporter\Parser\Json;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;
use Application\ImportBundle\Reader\Json\JsonReaderInterface;

/**
 * Tickets json file parser.
 *
 * Class Tickets
 */
final class Tickets extends AbstractParser
{
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
        return $this->reader->getDirectoryFilesCount(JsonReaderInterface::ENTITY_TICKET_PATH, $this->getBatchNum());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($this->reader->getData(JsonReaderInterface::ENTITY_TICKET_PATH, $this->getBatchNum()))
            ->setPrefix('JSONTicket')
            ->setRefColumn('oid')
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
        $formatted = $this->formatter->format($data, array(
            'oid'            => TransformerInterface::TYPE_STRING,
            'import_map_key' => TransformerInterface::TYPE_STRING,
            'destination'    => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix'     => 'ticket_',
                'ref'        => 'oid',
            )),
            'ref'           => TransformerInterface::TYPE_STRING,
            'department'    => TransformerInterface::TYPE_STRING,
            'person'        => TransformerInterface::TYPE_STRING,
            'agent'         => TransformerInterface::TYPE_STRING,
            'agent_team'    => TransformerInterface::TYPE_STRING,
            'status'        => TransformerInterface::TYPE_STRING,
            'date_created'  => TransformerInterface::TYPE_DATE,
            'date_resolved' => TransformerConfiguration::create(TransformerInterface::TYPE_DATE, array(
                'null'      => true,
            )),
            'date_archived' => TransformerConfiguration::create(TransformerInterface::TYPE_DATE, array(
                'null'      => true,
            )),
            'subject'       => TransformerInterface::TYPE_STRING,
            'priority'      => TransformerInterface::TYPE_ARRAY,
            'language'      => TransformerInterface::TYPE_STRING,
            'category'      => TransformerInterface::TYPE_STRING,
            'workflow'      => TransformerInterface::TYPE_STRING,
            'product'       => TransformerInterface::TYPE_STRING,
            'organization'  => TransformerInterface::TYPE_STRING,
            'is_hold'       => TransformerInterface::TYPE_BOOLEAN,
            'urgency'       => TransformerInterface::TYPE_INT,
            'messages'      => TransformerInterface::TYPE_ARRAY,
            'participants'  => TransformerInterface::TYPE_ARRAY,
            'labels'        => TransformerInterface::TYPE_ARRAY,
            'custom_fields' => TransformerInterface::TYPE_ARRAY,
            'log_message'   => TransformerInterface::TYPE_STRING,
        ));

        $entity = new Entity\Ticket();
        $entity
            ->setRawData($data)
            ->setOid($formatted['oid'])
            ->setImportMapKey($formatted['import_map_key'])
            ->setDestination($formatted['destination'])
            ->setRef($formatted['ref'])
            ->setDepartment($formatted['department'])
            ->setPersonEmail($formatted['person'])
            ->setAgentEmail($formatted['agent'])
            ->setAgentTeam($formatted['agent_team'])
            ->setStatus($formatted['status'])
            ->setSubject($formatted['subject'])
            ->setPriority($this->exportPriority($formatted['priority']))
            ->setLanguage($formatted['language'])
            ->setCategory($formatted['category'])
            ->setWorkflow($formatted['workflow'])
            ->setProduct($formatted['product'])
            ->setOrganization($formatted['organization'])
            ->setAsHold($formatted['is_hold'])
            ->setUrgency($formatted['urgency'])
            ->setDateCreated($formatted['date_created'])
            ->setDateResolved($formatted['date_resolved'])
            ->setDateArchived($formatted['date_archived'])
            ->setLogMessage($formatted['log_message'])
        ;

        foreach ($formatted['labels'] as $label) {
            $entity->addLabel($label);
        }
        foreach ($formatted['participants'] as $participant) {
            $entity->addParticipant($participant);
        }

        $messages = $this->exportMessages($formatted['messages']);
        foreach ($messages as $message) {
            $entity->addMessage($message);
        }

        $custom_fields = $this->getCustomFieldsParser()->export($formatted['custom_fields']);
        foreach ($custom_fields as $custom_field) {
            $entity->addCustomField($custom_field);
        }

        return $entity;
    }

    /**
     * Returns a ticket priority entity.
     *
     * @param array $data
     *
     * @return Entity\TicketPriority|null
     */
    private function exportPriority(array $data = null)
    {
        if (empty($data)) {
            return;
        }

        $configuration = array(
            'oid'         => TransformerInterface::TYPE_STRING,
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix'  => 'priority_',
                'ref'     => 'oid',
            )),
            'title' => TransformerInterface::TYPE_STRING,
            'value' => TransformerInterface::TYPE_STRING,
        );

        $formatted = $this->formatter->format($data, $configuration);
        $entity    = new Entity\TicketPriority();
        $entity
            ->setRawData($data)
            ->setDestination($formatted['destination'])
            ->setOid($formatted['oid'])
            ->setTitle($formatted['title'])
            ->setValue($formatted['value'])
        ;

        return $entity;
    }

    /**
     * Returns a collection of the ticket messages.
     *
     * @param array $messages
     *
     * @return Entity\TicketMessage[]
     */
    private function exportMessages(array $messages)
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($messages)
            ->setPrefix('JSONTicketMessage')
            ->setRefColumn('oid')
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
        $formatted = $this->formatter->format($data, array(
            'oid'         => TransformerInterface::TYPE_STRING,
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix'  => 'message_',
                'ref'     => 'oid',
            )),
            'person'       => TransformerInterface::TYPE_STRING,
            'date_created' => TransformerInterface::TYPE_DATE,
            'message_text' => TransformerInterface::TYPE_STRING,
            'message_html' => TransformerInterface::TYPE_STRING,
            'is_note'      => TransformerInterface::TYPE_BOOLEAN,
            'attachments'  => TransformerInterface::TYPE_ARRAY,
        ));

        $entity = new Entity\TicketMessage();
        $entity
            ->setRawData($data)
            ->setOid($formatted['oid'])
            ->setDestination($formatted['destination'])
            ->setPersonEmail($formatted['person'])
            ->setMessageText($formatted['message_text'])
            ->setMessageHtml($formatted['message_html'])
            ->setAsNote($formatted['is_note'])
            ->setDateCreated($formatted['date_created'])
        ;

        $attachments = $this->getAttachmentParser()->exportAttachments($formatted['attachments']);
        foreach ($attachments as $attachment) {
            /* @var Entity\Attachment $attachment */
            $entity->addAttachment($attachment);
        }

        return $entity;
    }
}
