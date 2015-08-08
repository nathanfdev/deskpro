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

namespace Application\ImportBundle\Generator\Exporter\Parser\ZenDesk;

use Application\ImportBundle\Entity;
use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Generator\Exporter\Formatter\FormatterInterface;
use Application\ImportBundle\Generator\Exporter\Parser\SkippingException;
use Application\ImportBundle\Reader\ZenDesk\ZenDeskReaderInterface;
use DateTime;
use Exception;
use Guzzle\Http\Client as HttpClient;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Exception\ServerErrorResponseException;
use Orb\Util\Strings;

/**
 * ZenDesk tickets parser
 *
 * Class Tickets
 * @package Application\ImportBundle\Generator\Exporter\Parser\ZenDesk
 */
final class Tickets extends AbstractParser
{
    const STATUS_NEW      = 'new';
    const STATUS_OPEN     = 'open';
    const STATUS_PENDING  = 'pending';
    const STATUS_HOLD     = 'hold';
    const STATUS_SOLVED   = 'solved';
    const STATUS_CLOSED   = 'closed';
    const STATUS_DELETED  = 'deleted';

    const PRIORITY_URGENT = 'urgent';
    const PRIORITY_HIGH   = 'high';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_LOW    = 'low';

    /**
     * @var TicketPeopleStorage
     */
    private $tickets_people;

    /**
     * @var TicketsMapper
     */
    private $tickets_mapper;

    /**
     * @var HttpClient
     */
    private $http_client;

    /**
     * Constructor
     *
     * @param ZenDeskReaderInterface       $reader
     * @param FormatterInterface           $formatter
     * @param TicketPeopleStorageInterface $people_storage
     * @param HttpClient                   $http_client
     * @param TicketsMapper                $tickets_mapper
     */
    public function __construct(
        ZenDeskReaderInterface       $reader,
        FormatterInterface           $formatter,
        TicketPeopleStorageInterface $people_storage,
        TicketsMapper                $tickets_mapper,
        HttpClient                   $http_client
    ) {
        parent::__construct($reader, $formatter);

        $this->tickets_people = $people_storage;
        $this->tickets_mapper = $tickets_mapper;
        $this->http_client    = $http_client;
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
        // We could read data from ZD reader twice because of ZD reader cache support
        return count($this->getTickets());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $tickets    = $this->getTickets();

        $collection = new Entity\Collection();
        $collection->setExpectedCount(count($tickets));

        foreach ($tickets as $num => $ticket) {
            $this->advanceProgressBar();
            $tid = @$ticket['id'] ?: '?';

            try {
                $entity = $this->exportTicket($ticket);
                if ($entity) {
                    $collection->attach($entity);
                } else {
                    $this->logDebugInfo(sprintf("[ZDTicket #%s] Invalid ticket entity", $tid), $ticket);
                    $this->logWarning(sprintf('[ZDTicket #%s] Invalid ticket record found (Skipping): Could not create entity', $tid));
                }

            } catch (SkippingException $e) {
                $this->logError(sprintf('[ZDTicket #%s] Invalid ticket record found (Skipping): %s', $tid, $e->getMessage()));

            } catch (\Exception $e) {
                $this->logDebugException(sprintf("Exception with ticket %d", $tid), $e, $ticket);
                $this->logError(sprintf('[ZDTicket #%s] Invalid ticket record found (Skipping): Unknown error: %s', $tid, $e->getMessage()));
            }
        }

        return $collection;
    }

    /**
     * Returns a ticket entity
     *
     * @param array $ticket
     *
     * @return Entity\Ticket
     * @throws SkippingException
     */
    private function exportTicket(array $ticket)
    {
        if ($this->isTicketValid($ticket)) {
            $person_email = $this->tickets_people->getPersonEmail($ticket['submitter_id']);
            $agent_email  = $this->tickets_people->getPersonEmail($ticket['assignee_id']);

            if ( ! $person_email) {
                throw new SkippingException(sprintf('Unable to get submitter email by id %s', $ticket['submitter_id']));
            }

            $ref = $this->tickets_mapper->findRefByOldId($ticket['id']);
            if ( ! $ref) {
                $ref = Strings::random(10, Strings::CHARS_ALPHANUM_IU);
                $this->tickets_mapper->saveMapping($ticket['id'], $ref);
            }

            $entity = new Entity\Ticket();
            $entity
                ->setRawData($ticket)
                ->setDestination('ticket_' . $ticket['id'])
                ->setOid($ticket['id'])
                ->setRef($ref)
                ->setPersonEmail($person_email)
                ->setAgentEmail($agent_email)
                ->setSubject($ticket['subject'] ? : 'No subject')
                ->setStatus($this->getStatus($ticket['status']))
                ->setOrganization($this->getOrganizationName($ticket['organization_id']))
                ->setPriority($this->exportPriority($ticket['priority']))
                ->setDateCreated(DateFormatter::transform($ticket['created_at'], $this->logger))
                ->setLogMessage(sprintf('Imported from ZenDesk (old ticket ID #%s)', $ticket['id']))
            ;

            switch ($ticket['status']) {
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

            foreach ($ticket['tags'] as $label) {
                $entity->addLabel($label);
            }
            foreach ($this->exportMessages($ticket) as $message) {
                $entity->addMessage($message);
            }

            return $entity;
        }

        return null;
    }

    /**
     * Returns a ticket priority entity
     *
     * @param string $priority
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

        return null;
    }

    /**
     * Returns a ticket comments entity collection
     *
     * @param array $ticket
     * @return Entity\TicketMessage[]
     */
    private function exportMessages(array $ticket)
    {
        $collection = new Entity\Collection();

        foreach ($ticket['comments'] as $num => $comment) {
            try {
                $entity = $this->exportMessage($comment);
                if ($entity) {
                    $collection->attach($entity);
                    $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));
                } else {
                    $this->logWarning(sprintf('Invalid ticket message attachment record found (Skipping): %d', $num));
                }

            } catch (NoColumnException $e) {
                $this->logError(sprintf(
                    'Invalid ticket message record `%d` found (Skipping): %s',
                    $num, $e->getMessage()
                ));
            }
        }

        return $collection;
    }

    /**
     * Returns a ticket comments entity
     *
     * @param array $comment
     * @return Entity\TicketMessage|null
     */
    private function exportMessage(array $comment)
    {
        if ($this->isMessageValid($comment)) {
            if (empty($comment['author_id'])) {
                $this->logError(sprintf('Comment #%d without author_id, skipping', $comment['id']));
                return null;
            }

            $author_email = $this->tickets_people->getPersonEmail($comment['author_id']);
            if ( ! $author_email) {
                $this->logError(sprintf('Unable to get comment author #%d, skipping', $comment['author_id']));
                return null;
            }

            $entity = new Entity\TicketMessage();
            $entity
                ->setDestination(DestinationFormatter::transform('message_', $comment['id']))
                ->setOid($comment['id'])
                ->setPersonEmail($author_email)
                ->setMessageText($comment['body'])
                ->setAsNote($comment['public'] === false)
                ->setDateCreated(DateFormatter::transform($comment['created_at'], $this->logger))
            ;

            $attachments = $this->exportAttachments($comment['attachments']);
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
            try {
                $request = $this->http_client->get($attachment['content_url']);
                $entity  = new Entity\Attachment();
                $entity
                    ->setDestination('attachment_' . $attachment['id'])
                    ->setOid($attachment['id'])
                    ->setBlobData(base64_encode($request->send()->getBody(true)))
                    ->setFileName($attachment['file_name'])
                    ->setContentType($attachment['content_type'])
                ;

                return $entity;

            } catch (ClientErrorResponseException $e) {
                $this->logError(sprintf('Unable to download attachment #%s', $attachment['id']));
                $this->logError($e->getMessage());

                $response = $e->getResponse();
                if ($response) {
                    $this->logError(sprintf('Status code: %s', $response->getStatusCode()));
                    $this->logError(sprintf('Reason phrase: %s', $response->getReasonPhrase()));
                }

            } catch (ServerErrorResponseException $e) {
                $this->logError(sprintf('Unable to download attachment #%s', $attachment['id']));
                $this->logError($e->getMessage());

                $response = $e->getResponse();
                if ($response) {
                    $this->logError(sprintf('Status code: %s', $response->getStatusCode()));
                    $this->logError(sprintf('Reason phrase: %s', $response->getReasonPhrase()));
                }
            }
        }

        return null;
    }

    /**
     * Returns tickets
     * Loads data from ZenDesk reader
     *
     * @return array
     * @throws Exception
     */
    private function getTickets()
    {
        $this->logDebugTimeStart('getTickets', "Reading tickets batch");

        $tickets = array();
        if ($this->getBatchConfig()->getTicketsEndTime() < new DateTime('-5 minutes')) {
            if ($this->getBatchConfig()->getTicketsEndTime()) {
                $this->logDebug(sprintf("Reading from time: %s", $this->getBatchConfig()->getTicketsEndTime()->format('Y-m-d H:i:s')));
            } else {
                $this->logDebug(sprintf("Reading from time: %s", "Beginning"));
            }

            $response = $this->reader->getTickets($this->getBatchConfig()->getTicketsEndTime());
            if (count($response)) {
                // ZenDesk API does not allow to get ticket comments in a single request due to huge response (could be ~20 MB)
                // We have to load comments for each ticket separately
                foreach ($response as $ticket) {
                    if ($ticket['status'] !== self::STATUS_DELETED) {
                        $this->logDebug(sprintf('[ZDTicket #%s] Reading comments', $ticket['id']));
                        $ticket['comments'] = $this->reader->getTicketComments($ticket['id']);

                        $tickets[] = $ticket;
                    } else {
                        $this->logDebug(sprintf('[ZDTicket #%s] Status deleted, skipping', $ticket['id']));
                    }
                }

                $this->tickets_people->loadByTickets($tickets);

                $this->end_time = $this->reader->getTicketsEndTime($this->getBatchConfig()->getTicketsEndTime());
                if ($this->end_time == $this->getBatchConfig()->getTicketsEndTime()) {
                    $this->end_time->modify('+1 second');
                }

                $this->logDebug(sprintf("New end time: %s", $this->end_time->format('Y-m-d H:i:s')));

            } else {
                $this->logDebug(sprintf("No more records"));
            }

        } else {
            $this->logAlert('No ticket was exported due 5 minutes timeout of the last end time');
        }

        $this->logDebug(sprintf("Read %d tickets", count($tickets)));
        $this->logDebugTimeEnd('getTickets', "Done reading tickets batch");

        return $tickets;
    }

    /**
     * Checks if ticket has all required columns
     *
     * @param array $ticket
     * @return bool
     *
     * @throws NoColumnException
     * @throws NotArrayException
     */
    private function isTicketValid(array $ticket)
    {
        $columns = array(
            'id',
            'submitter_id',
            'assignee_id',
            'subject',
            'description',
            'status',
            'priority',
            'organization_id',
            'created_at',
            'custom_fields',
            'tags',
            'comments',
        );

        return ColumnHelper::hasRequiredColumns($ticket, $columns)
            && ColumnHelper::isArrayColumn($ticket, 'custom_fields')
            && ColumnHelper::isArrayColumn($ticket, 'tags')
            && ColumnHelper::isArrayColumn($ticket, 'comments');
    }

    /**
     * Checks if ticket reply has all required columns
     *
     * @param array $comment
     * @return bool
     *
     * @throws NoColumnException
     * @throws NotArrayException
     */
    private function isMessageValid(array $comment)
    {
        $columns = array(
            'id',
            'author_id',
            'body',
            'public',
            'created_at',
            'attachments',
        );

        return ColumnHelper::hasRequiredColumns($comment, $columns)
            && ColumnHelper::isArrayColumn($comment, 'attachments');
    }

    /**
     * Check if ticket message attachment has all required columns
     *
     * @param array $attachment
     * @return bool
     *
     * @throws NoColumnException
     */
    private function isAttachmentValid(array $attachment)
    {
        $columns = array(
            'id',
            'file_name',
            'content_type',
            'content_url',
            'inline',
        );

        return ColumnHelper::hasRequiredColumns($attachment, $columns) && $attachment['inline'] === false;
    }

    /**
     * Returns DeskPRO status by ZenDesk status
     *
     * @param string $status
     *
     * @return string
     * @throws Exception
     */
    private function getStatus($status)
    {
        $map = array(
            self::STATUS_NEW     => DeskPROEntity\Ticket::STATUS_AWAITING_AGENT,
            self::STATUS_OPEN    => DeskPROEntity\Ticket::STATUS_AWAITING_AGENT,
            self::STATUS_PENDING => DeskPROEntity\Ticket::STATUS_AWAITING_AGENT,
            self::STATUS_HOLD    => DeskPROEntity\Ticket::STATUS_AWAITING_USER,
            self::STATUS_SOLVED  => DeskPROEntity\Ticket::STATUS_RESOLVED,
            self::STATUS_CLOSED  => DeskPROEntity\Ticket::STATUS_ARCHIVED,
            self::STATUS_DELETED => DeskPROEntity\Ticket::STATUS_HIDDEN . '.' . DeskPROEntity\Ticket::HIDDEN_STATUS_DELETED,
        );

        if (isset($map[$status])) {
            return $map[$status];
        }

        throw new Exception(sprintf('Ticket status `%s` not found', $status));
    }
}
