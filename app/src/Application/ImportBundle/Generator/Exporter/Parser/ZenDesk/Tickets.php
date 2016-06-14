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

namespace Application\ImportBundle\Generator\Exporter\Parser\ZenDesk;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\FormatterInterface;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;
use Application\ImportBundle\Generator\Exporter\Parser\ParserHelperSet;
use Application\ImportBundle\Generator\Exporter\Parser\ParserPeopleStorageInterface;
use Application\ImportBundle\Generator\Exporter\Parser\SkippingException;
use Application\ImportBundle\Reader\ZenDesk\ZenDeskReaderInterface;
use DateTime;
use Exception;
use Orb\Util\Strings;

/**
 * ZenDesk tickets parser.
 *
 * Class Tickets
 */
final class Tickets extends AbstractParser
{
    const STATUS_NEW     = 'new';
    const STATUS_OPEN    = 'open';
    const STATUS_PENDING = 'pending';
    const STATUS_HOLD    = 'hold';
    const STATUS_SOLVED  = 'solved';
    const STATUS_CLOSED  = 'closed';
    const STATUS_DELETED = 'deleted';

    const PRIORITY_URGENT = 'urgent';
    const PRIORITY_HIGH   = 'high';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_LOW    = 'low';

    /**
     * @var ParserPeopleStorageInterface
     */
    private $tickets_people;

    /**
     * Constructor.
     *
     * @param ZenDeskReaderInterface       $reader
     * @param FormatterInterface           $formatter
     * @param ParserHelperSet              $helpers
     * @param ParserPeopleStorageInterface $people_storage
     */
    public function __construct(
        ZenDeskReaderInterface       $reader,
        FormatterInterface           $formatter,
        ParserHelperSet              $helpers,
        ParserPeopleStorageInterface $people_storage
    ) {
        parent::__construct($reader, $formatter, $helpers);
        $this->tickets_people = $people_storage;
    }

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
        // We can read data from ZD reader twice because of ZD reader cache support
        return count($this->getTickets(true));
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($this->getTickets())
            ->setPrefix('ZDTicket')
            ->setRefColumn('id')
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
     * @throws SkippingException
     *
     * @return Entity\Ticket
     */
    protected function exportTicket(array $data)
    {
        $entity    = new Entity\Ticket();
        $formatted = $this->formatter->format($data, array(
            'id'          => TransformerInterface::TYPE_STRING,
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix'  => $entity->getDestinationPrefix(),
                'ref'     => 'id',
            )),
            'requester_id'     => TransformerInterface::TYPE_STRING,
            'assignee_id'      => TransformerInterface::TYPE_STRING,
            'collaborator_ids' => TransformerInterface::TYPE_ARRAY,
            'subject'          => TransformerInterface::TYPE_STRING,
            'description'      => TransformerInterface::TYPE_STRING,
            'status'           => TransformerInterface::TYPE_STRING,
            'priority'         => TransformerInterface::TYPE_STRING,
            'organization_id'  => TransformerInterface::TYPE_STRING,
            'created_at'       => TransformerInterface::TYPE_DATE,
            'custom_fields'    => TransformerInterface::TYPE_ARRAY,
            'tags'             => TransformerInterface::TYPE_ARRAY,
            'comments'         => TransformerInterface::TYPE_ARRAY,
        ));

        $person_email = $this->tickets_people->getPersonEmail($formatted['requester_id']);
        $agent_email  = $this->tickets_people->getPersonEmail($formatted['assignee_id']);

        if (!$person_email) {
            throw new SkippingException(sprintf('Unable to get submitter email by id #%s', $formatted['requester_id']), $formatted);
        }

        $entity
            ->setRawData($data)
            ->setDestination($formatted['destination'])
            ->setOid($formatted['id'])
            ->setImportMapKey(DeskPROEntity\ImportMap::TYPE_ZENDESK_TICKET)
            ->setRef(Strings::random(10, Strings::CHARS_ALPHANUM_IU))
            ->setPersonEmail($person_email)
            ->setAgentEmail($agent_email)
            ->setSubject($formatted['subject'] ?: 'No subject')
            ->setStatus($this->getStatus($formatted['status']))
            ->setOrganization($this->getOrganizationName($formatted['organization_id']))
            ->setPriority($this->exportPriority($formatted['priority']))
            ->setDateCreated($formatted['created_at'])
            ->setLogMessage(sprintf('Imported from ZenDesk (old ticket ID #%s)', $formatted['id']))
        ;

        switch ($formatted['status']) {
            case self::STATUS_HOLD:
                $entity->setAsHold(true);
                break;
            case self::STATUS_SOLVED:
                $entity->setDateResolved(new DateTime());
                break;
            case self::STATUS_CLOSED:
                $entity->setDateArchived(new DateTime());
                break;
        }

        foreach ($formatted['tags'] as $label) {
            $entity->addLabel($label);
        }
        foreach ($formatted['collaborator_ids'] as $collaborator_id) {
            $participant_email = $this->tickets_people->getPersonEmail($collaborator_id);
            if ($participant_email) {
                $entity->addParticipant($participant_email);
            } else {
                $this->logWarning(sprintf('Unable to get participant email, id = %s', $collaborator_id));
            }
        }
        foreach ($this->exportMessages($formatted) as $message) {
            $entity->addMessage($message);
        }
        foreach ($this->exportCustomFields($formatted) as $custom_field) {
            $entity->addCustomField($custom_field);
        }

        return $entity;
    }

    /**
     * Returns a ticket priority entity.
     *
     * @param string $priority
     *
     * @return Entity\TicketPriority|null
     */
    private function exportPriority($priority)
    {
        $mapping = array(
            self::PRIORITY_URGENT => 10,
            self::PRIORITY_HIGH   => 5,
            self::PRIORITY_NORMAL => 2,
            self::PRIORITY_LOW    => 1,
        );

        if ($priority) {
            if (in_array($priority, array_keys($mapping), true)) {
                $entity = new Entity\TicketPriority();
                $entity
                    ->setDestination('priority')
                    ->setOid(0)
                    ->setTitle($priority)
                    ->setValue($mapping[$priority])
                ;

                return $entity;
            } else {
                $this->logWarning(sprintf('Unknown priority `%s`', $priority));
            }
        }

        return;
    }

    /**
     * Returns a ticket comments entity collection.
     *
     * @param array $ticket
     *
     * @return Entity\TicketMessage[]
     */
    private function exportMessages(array $ticket)
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($ticket['comments'])
            ->setPrefix('ZDTicketComment')
            ->setRefColumn('id')
            ->setMethod('exportMessage')
        ;

        return $this->exportCollection($config);
    }

    /**
     * Returns a ticket comments entity.
     *
     * @param array $data
     *
     * @return Entity\TicketMessage|null
     */
    protected function exportMessage(array $data)
    {
        $entity    = new Entity\TicketMessage();
        $formatted = $this->formatter->format($data, array(
            'id'          => TransformerInterface::TYPE_STRING,
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix'  => $entity->getDestinationPrefix(),
                'ref'     => 'id',
            )),
            'author_id'   => TransformerInterface::TYPE_STRING,
            'body'        => TransformerInterface::TYPE_STRING,
            'public'      => TransformerInterface::TYPE_BOOLEAN,
            'created_at'  => TransformerInterface::TYPE_DATE,
            'attachments' => TransformerInterface::TYPE_ARRAY,
        ));

        if (empty($formatted['author_id'])) {
            throw new SkippingException('Comment without author_id, skipping', $formatted);
        }

        $author_email = $this->tickets_people->getPersonEmail($formatted['author_id']);
        if (!$author_email) {
            throw new SkippingException('Unable to get comment author, skipping', $formatted);
        }

        $entity
            ->setRawData($data)
            ->setDestination($formatted['destination'])
            ->setOid($formatted['id'])
            ->setImportMapKey(DeskPROEntity\ImportMap::TYPE_ZENDESK_TICKET_MESSAGE)
            ->setPersonEmail($author_email)
            ->setMessageText($formatted['body'])
            ->setAsNote($formatted['public'] === false)
            ->setDateCreated($formatted['created_at'])
        ;

        $attachments = $this->getAttachmentParser()->export($formatted['attachments']);
        foreach ($attachments as $attachment) {
            $entity->addAttachment($attachment);
        }

        return $entity;
    }

    /**
     * Returns a ticket custom field entity collection.
     *
     * @param array $ticket
     *
     * @return Entity\Collection|Entity\CustomField[]
     */
    protected function exportCustomFields(array $ticket)
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($ticket['custom_fields'])
            ->setPrefix('ZDTicketCustomField')
            ->setRefColumn('id')
            ->setMethod('exportCustomField')
        ;

        return $this->exportCollection($config);
    }

    /**
     * Returns a ticket custom field entity.
     *
     * @param array $data
     *
     * @return Entity\CustomField
     */
    protected function exportCustomField(array $data)
    {
        $entity    = new Entity\CustomField();
        $formatted = $this->formatter->format($data, array(
            'id'          => TransformerInterface::TYPE_STRING,
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix'  => $entity->getDestinationPrefix(),
                'ref'     => 'id',
            )),
            'value' => TransformerInterface::TYPE_STRING,
        ));

        $custom_def = $this->getCustomDefById($formatted['id']);
        if (!empty($custom_def['custom_field_options'])) {
            foreach ($custom_def['custom_field_options'] as $option) {
                if ($option['value'] == $formatted['value']) {
                    $formatted['value'] = $option['name'];
                }
            }
        }
        if (!empty($custom_def['system_field_options'])) {
            foreach ($custom_def['system_field_options'] as $option) {
                if ($option['value'] == $formatted['value']) {
                    $formatted['value'] = $option['name'];
                }
            }
        }

        $entity
            ->setRawData($data)
            ->setOid($formatted['id'])
            ->setDestination($formatted['destination'])
            ->setKey($custom_def['title_in_portal'] ?: $custom_def['title'])
            ->setValue($formatted['value'] ?: '')
        ;

        return $entity;
    }

    /**
     * Returns ticket custom def by key.
     *
     * @param int $id
     *
     * @return array
     */
    protected function getCustomDefById($id)
    {
        $custom_defs = $this->reader->getTicketFields();
        foreach ($custom_defs as $custom_def) {
            if ($custom_def['id'] == $id) {
                return $custom_def;
            }
        }

        throw new SkippingException(sprintf('No custom def found with id=%s', $id), $custom_defs);
    }

    /**
     * Returns tickets
     * Loads data from ZenDesk reader.
     *
     * @param bool $count_only
     *
     * @throws Exception
     *
     * @return array
     */
    private function getTickets($count_only = false)
    {
        $this->logDebugTimeStart('getTickets', 'Reading tickets batch');

        $tickets    = array();
        $start_time = $this->getBatchConfig()->getTicketsEndTime();

        if ($start_time < new DateTime('-5 minutes')) {
            if ($start_time) {
                $this->logDebug(sprintf('Reading from time: %s', $start_time->format('Y-m-d H:i:s')));
            } else {
                $this->logDebug(sprintf('Reading from time: %s', 'Beginning'));
            }

            $response = $this->reader->getTickets($start_time);

            if (count($response)) {
                // ZenDesk API does not allow to get ticket comments in a single request due to huge response (could be up to ~20 MB)
                // We have to load comments for each ticket separately
                foreach ($response as $ticket) {
                    if ($ticket['status'] !== self::STATUS_DELETED) {
                        if (!$count_only) {
                            $this->logDebug(sprintf('[ZDTicket #%s] Reading comments', $ticket['id']));
                            $ticket['comments'] = $this->reader->getTicketComments($ticket['id']);
                        }

                        $tickets[] = $ticket;
                    } else {
                        $this->logDebug(sprintf('[ZDTicket #%s] Status deleted, skipping', $ticket['id']));
                    }
                }

                $this->tickets_people->loadBy($tickets);

                $this->end_time = $this->reader->getTicketsEndTime($start_time);
                if ($this->end_time == $start_time) {
                    $this->end_time->modify('+1 second');
                }

                $this->logDebug(sprintf('New end time: %s', $this->end_time->format('Y-m-d H:i:s')));
            } else {
                $this->logDebug(sprintf('No more records'));
            }
        } else {
            $this->logAlert('No ticket was exported due 5 minutes timeout of the last end time');
        }

        $this->logDebug(sprintf('Read %d tickets', count($tickets)));
        $this->logDebugTimeEnd('getTickets', 'Done reading tickets batch');

        return $tickets;
    }

    /**
     * Returns DeskPRO status by ZenDesk status.
     *
     * @param string $status
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    private function getStatus($status)
    {
        $map = array(
            self::STATUS_NEW     => DeskPROEntity\Ticket::STATUS_AWAITING_AGENT,
            self::STATUS_OPEN    => DeskPROEntity\Ticket::STATUS_AWAITING_AGENT,
            self::STATUS_PENDING => DeskPROEntity\Ticket::STATUS_AWAITING_USER,
            self::STATUS_HOLD    => DeskPROEntity\Ticket::STATUS_AWAITING_AGENT,
            self::STATUS_SOLVED  => DeskPROEntity\Ticket::STATUS_RESOLVED,
            self::STATUS_CLOSED  => DeskPROEntity\Ticket::STATUS_ARCHIVED,
            self::STATUS_DELETED => DeskPROEntity\Ticket::STATUS_HIDDEN.'.'.DeskPROEntity\Ticket::HIDDEN_STATUS_DELETED,
        );

        if (isset($map[$status])) {
            return $map[$status];
        }

        throw new \RuntimeException(sprintf('Ticket status `%s` not found', $status));
    }
}
