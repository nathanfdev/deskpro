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
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerException;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\SkippingException;
use Application\ImportBundle\Reader\ZenDesk\ZenDeskReaderInterface;
use DateTime;
use Exception;
use Guzzle\Http\Client as HttpClient;
use Guzzle\Http\Exception\BadResponseException;
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
     * @var OidMapper
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
     * @param ParserPeopleStorageInterface $people_storage
     * @param HttpClient                   $http_client
     * @param OidMapper                $tickets_mapper
     */
    public function __construct(
        ZenDeskReaderInterface       $reader,
        FormatterInterface           $formatter,
        ParserPeopleStorageInterface $people_storage,
        OidMapper                    $tickets_mapper,
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
        // We can read data from ZD reader twice because of ZD reader cache support
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

        foreach ($tickets as $num => $data) {
            $this->advanceProgressBar();

            try {
                $entity = $this->exportTicket($data);

                $collection->attach($entity);
                $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));

            } catch (SkippingException $e) {
                $this->logSkippingException('ZDTicket', $this->getEntityType(), 'id', $e);
            } catch (TransformerException $e) {
                $this->logTransformerException('ZDTicket', $this->getEntityType(), 'id', $e);
            } catch (\Exception $e) {
                $this->logUnknownException('ZDTicket', $this->getEntityType(), 'id', $e, $data);
            }
        }

        return $collection;
    }

    /**
     * Returns a ticket entity
     *
     * @param array $data
     *
     * @return Entity\Ticket
     * @throws SkippingException
     */
    private function exportTicket(array $data)
    {
        $formatted = $this->formatter->format($data, array(
            'id'              => TransformerInterface::TYPE_STRING,
            'destination'     => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix' => 'ticket_',
                'ref'    => 'id',
            )),
            'submitter_id'    => TransformerInterface::TYPE_STRING,
            'assignee_id'     => TransformerInterface::TYPE_STRING,
            'subject'         => TransformerInterface::TYPE_STRING,
            'description'     => TransformerInterface::TYPE_STRING,
            'status'          => TransformerInterface::TYPE_STRING,
            'priority'        => TransformerInterface::TYPE_STRING,
            'organization_id' => TransformerInterface::TYPE_STRING,
            'created_at'      => TransformerInterface::TYPE_DATE,
            'custom_fields'   => TransformerInterface::TYPE_ARRAY,
            'tags'            => TransformerInterface::TYPE_ARRAY,
            'comments'        => TransformerInterface::TYPE_ARRAY,
        ));

        $person_email = $this->tickets_people->getPersonEmail($formatted['submitter_id']);
        $agent_email  = $this->tickets_people->getPersonEmail($formatted['assignee_id']);

        if ( ! $person_email) {
            throw new SkippingException(sprintf('Unable to get submitter email by id #%s', $formatted['submitter_id']), $formatted);
        }

        $ref = $this->tickets_mapper->findRefByOldId($formatted['id']);
        if ( ! $ref) {
            $ref = Strings::random(10, Strings::CHARS_ALPHANUM_IU);
            $this->tickets_mapper->saveMapping($formatted['id'], $ref);
        }

        $entity = new Entity\Ticket();
        $entity
            ->setRawData($data)
            ->setDestination($formatted['destination'])
            ->setOid($formatted['id'])
            ->setRef($ref)
            ->setPersonEmail($person_email)
            ->setAgentEmail($agent_email)
            ->setSubject($formatted['subject'] ? : 'No subject')
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
        foreach ($this->exportMessages($formatted) as $message) {
            $entity->addMessage($message);
        }

        return $entity;
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

        foreach ($ticket['comments'] as $num => $data) {
            try {
                $entity = $this->exportMessage($data);

                $collection->attach($entity);
                $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));

            } catch (SkippingException $e) {
                $this->logSkippingException('ZDTicketComment', 'ticket message', 'id', $e);
            } catch (TransformerException $e) {
                $this->logTransformerException('ZDTicketComment', 'ticket message', 'id', $e);
            } catch (\Exception $e) {
                $this->logUnknownException('ZDTicketComment', 'ticket message', 'id', $e, $data);
            }
        }

        return $collection;
    }

    /**
     * Returns a ticket comments entity
     *
     * @param array $data
     * @return Entity\TicketMessage|null
     */
    private function exportMessage(array $data)
    {
        $formatted = $this->formatter->format($data, array(
            'id'          => TransformerInterface::TYPE_STRING,
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix' => 'message_',
                'ref'    => 'id',
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
        if ( ! $author_email) {
            throw new SkippingException('Unable to get comment author, skipping', $formatted);
        }

        $entity = new Entity\TicketMessage();
        $entity
            ->setRawData($data)
            ->setDestination($formatted['destination'])
            ->setOid($formatted['id'])
            ->setPersonEmail($author_email)
            ->setMessageText($formatted['body'])
            ->setAsNote($formatted['public'] === false)
            ->setDateCreated($formatted['created_at'])
        ;

        $attachments = $this->exportAttachments($formatted['attachments']);
        foreach ($attachments as $attachment) {
            /** @var Entity\Attachment $attachment */
            $entity->addAttachment($attachment);
        }

        return $entity;
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

        foreach ($attachments as $num => $data) {
            try {
                $entity = $this->exportAttachment($data);

                $collection->attach($entity);
                $this->logInfo(sprintf('Entity `%s` parsed successfully!', $entity->getDestination()));

            } catch (SkippingException $e) {
                $this->logSkippingException('ZDTicketCommentAttachment', 'ticket message attachment', 'id', $e);
            } catch (TransformerException $e) {
                $this->logTransformerException('ZDTicketCommentAttachment', 'ticket message attachment', 'id', $e);
            } catch (\Exception $e) {
                $this->logUnknownException('ZDTicketCommentAttachment', 'ticket message attachment', 'id', $e, $data);
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
        $formatted = $this->formatter->format($data, array(
            'id'           => TransformerInterface::TYPE_STRING,
            'destination'  => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix' => 'attachment_',
                'ref'    => 'id',
            )),
            'file_name'    => TransformerInterface::TYPE_STRING,
            'content_type' => TransformerInterface::TYPE_STRING,
            'content_url'  => TransformerInterface::TYPE_STRING,
            'inline'       => TransformerInterface::TYPE_BOOLEAN,
        ));

        if ($formatted['inline']) {
            throw new SkippingException('Inline attachment, skipping', $formatted);
        }

        try {
            $request   = $this->http_client->get($formatted['content_url']);
            $blob_data = base64_encode($request->send()->getBody(true));

        } catch (BadResponseException $e) {
            $this->logError($e->getMessage());

            $response = $e->getResponse();
            if ($response) {
                $this->logError(sprintf('Status code: %s', $response->getStatusCode()));
                $this->logError(sprintf('Reason phrase: %s', $response->getReasonPhrase()));
            }

            throw new SkippingException('Unable to download attachment', $formatted);
        }

        $entity = new Entity\Attachment();
        $entity
            ->setRawData($data)
            ->setDestination($formatted['destination'])
            ->setOid($formatted['id'])
            ->setBlobData($blob_data)
            ->setFileName($formatted['file_name'])
            ->setContentType($formatted['content_type'])
        ;

        return $entity;
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

                $this->tickets_people->loadBy($tickets);

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
