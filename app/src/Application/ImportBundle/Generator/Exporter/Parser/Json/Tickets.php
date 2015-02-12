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

use Application\ImportBundle\Generator\Exporter\Parser\NoColumnException;
use Application\ImportBundle\Generator\Exporter\Parser\NotArrayException;
use Application\ImportBundle\Generator\Writer\Json\Destination;
use Application\ImportBundle\Entity;
use DateTime;

/**
 * Tickets json file parser
 *
 * Class Tickets
 * @package Application\ImportBundle\Generator\Exporter\Parser\Json
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
        return $this->reader->getDirectoryFilesCount($this->getConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $collection = new Entity\Collection();
        $tickets    = $this->reader->getData($this->getConfig());

        foreach ($tickets as $num => $ticket) {
            $this->advanceProgressBar();

            try {
                $entity = $this->exportTicket($ticket);
                if ($entity) {
                    $collection->attach($entity);
                    $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
                } else {
                    $this->logWarning(sprintf('Invalid ticket record found (Skipping): %d', $num));
                }

            } catch (NoColumnException $e) {
                $this->logError(sprintf(
                    'Invalid ticket record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));

            } catch (NotArrayException $e) {
                $this->logError(sprintf(
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
                ->setDestination('ticket_' . $ticket['oid'])
                ->setOid($ticket['oid'])
                ->setRef($ticket['ref'])
                ->setDepartment($ticket['department'])
                ->setPersonEmail($ticket['person'])
                ->setAgentEmail($ticket['agent'])
                ->setAgentTeam($ticket['agent_team'])
                ->setStatus($ticket['status'])
                ->setDateCreated(new DateTime($ticket['date_created']))
                ->setSubject($ticket['subject'])
                ->setPriority($ticket['priority'])
                ->setLanguage($ticket['language'])
                ->setCategory($ticket['category'])
                ->setWorkflow($ticket['workflow'])
                ->setProduct($ticket['product'])
                ->setOrganization($ticket['organization'])
                ->setAsHold($ticket['is_hold'])
                ->setUrgency($ticket['urgency']);

            if ($ticket['date_published']) {
                $entity->setDateResolved(new DateTime($ticket['date_resolved']));
            }
            if ($ticket['date_archived']) {
                $entity->setDateArchived(new DateTime($ticket['date_archived']));
            }

            foreach ($ticket['labels'] as $label) {
                $entity->addLabel($label);
            }
            foreach ($ticket['participants'] as $participant) {
                $entity->addParticipant($participant);
            }

            $messages = $this->exportMessages($ticket['messages']);
            foreach ($messages as $message) {
                /** @var Entity\TicketMessage $message */
                $entity->addMessage($message);
            }
            $custom_fields = $this->exportCustomFields($ticket['custom_fields']);
            foreach ($custom_fields as $custom_field) {
                /** @var Entity\CustomField $custom_field */
                $entity->addCustomField($custom_field);
            }

            return $entity;
        }

        return null;
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
            try {
                $entity = $this->exportMessage($message);
                if ($entity) {
                    $collection->attach($entity);
                    $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
                } else {
                    $this->logWarning(sprintf('Invalid ticket message record found (Skipping): %d', $num));
                }

            } catch (NoColumnException $e) {
                $this->logError(sprintf(
                    'Invalid ticket message record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));

            } catch (NotArrayException $e) {
                $this->logError(sprintf(
                    'Invalid ticket message record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));
            }
        }

        return $collection;
    }

    private function exportMessage(array $message)
    {
        if ($this->isMessageValid($message)) {
            $entity = new Entity\TicketMessage();
            $entity
                ->setDestination('message_' . $message['oid'])
                ->setOid($message['oid'])
                ->setPersonEmail($message['person'])
                ->setMessageText($message['message_text'])
                ->setMessageHtml($message['message_html'])
                ->setAsNote($message['is_note'])
                ->setDateCreated(new DateTime($message['date_created']));

            $attachments = $this->exportAttachments($message['attachments']);
            foreach ($attachments as $attachment) {
                /** @var Entity\Attachment $attachment */
                $entity->addAttachment($attachment);
            }

            return $entity;
        }

        return null;
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
            try {
                $entity = $this->exportAttachment($attachment);
                if ($entity) {
                    $collection->attach($entity);
                    $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
                } else {
                    $this->logWarning(sprintf('Invalid ticket message attachment record found (Skipping): %d', $num));
                }

            } catch (NoColumnException $e) {
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
     * @param array $attachment
     * @return Entity\Attachment|null
     */
    private function exportAttachment(array $attachment)
    {
        if ($this->isAttachmentValid($attachment)) {
            $entity = new Entity\Attachment();
            $entity
                ->setOid($attachment['oid'])
                ->setPersonEmail($attachment['person'])
                ->setBlobData($attachment['blob_data'])
                ->setBlobData($attachment['blob_url'])
                ->setBlobData($attachment['blob_path'])
                ->setFileName($attachment['file_name'])
                ->setContentType($attachment['content_type'])
                ->setAsInline($attachment['is_inline']);

            return $entity;
        }

        return null;
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
    private function isTicketValid(array $ticket)
    {
        $columns = array(
            'oid',
            'ref',
            'department',
            'person',
            'agent',
            'agent_team',
            'status',
            'date_created',
            'date_resolved',
            'date_archived',
            'subject',
            'priority',
            'language',
            'category',
            'workflow',
            'product',
            'organization',
            'is_hold',
            'urgency',
            'messages',
            'participants',
            'labels',
            'custom_fields',
        );

        return $this->hasRequiredColumns($ticket, $columns)
            && $this->isArrayColumn($ticket, 'messages')
            && $this->isArrayColumn($ticket, 'participants')
            && $this->isArrayColumn($ticket, 'labels')
            && $this->isArrayColumn($ticket, 'custom_fields');
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
            'oid',
            'person',
            'date_created',
            'message_text',
            'message_html',
            'is_note',
            'attachments',
        );

        return $this->hasRequiredColumns($message, $columns)
            && $this->isArrayColumn($message, 'attachments');
    }
}
