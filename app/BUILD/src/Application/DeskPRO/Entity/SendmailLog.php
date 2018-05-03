<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Orb\Util\Arrays;
use Orb\Util\Strings;

/**
 * A log of a message sent to a person.
 *
 * This is a log of exactly 1 email message sent to exactly 1 person.
 * For example, if one email was sent to 1 person and 3 CC'd people, then there
 * would be 4 SendmailLog's.
 */
class SendmailLog extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Person
     */
    protected $person;

    /**
     * @var Ticket
     */
    protected $ticket;

    /**
     * @var TicketMessage
     */
    protected $ticket_message;

    /**
     * @var string
     */
    protected $to_address;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $subject = '';

    /**
     * @var string
     */
    protected $from_address;

    /**
     * @var \DateTime
     */
    protected $date_created;

    /**
     * @var \DateTime
     */
    protected $date_process;

    //### Delivered
    /**
     * @var \DateTime
     */
    protected $date_deliver;

    /**
     * @var string
     */
    protected $reason_deliver;

    //### Opened/Clicks
    /**
     * @var \DateTime
     */
    protected $date_open;

    /**
     * @var \DateTime
     */
    protected $date_click;

    /**
     * A plaintext log with multiple lines: [date] URL.
     *
     * @var string
     */
    protected $clicked_urls;

    /**
     * @var int
     */
    protected $count_open = 0;

    /**
     * @var int
     */
    protected $count_click = 0;

    //### Deferred
    /**
     * @var \DateTime
     */
    protected $date_defer;

    /**
     * @var string
     */
    protected $reason_defer;

    //### Bounced/Spam
    /**
     * @var \DateTime
     */
    protected $date_bounce;

    /**
     * @var string
     */
    protected $bounce_type;

    /**
     * @var string
     */
    protected $bounce_code;

    /**
     * @var string
     */
    protected $reason_bounce;

    /**
     * @var \DateTime
     */
    protected $date_drop;

    /**
     * @var string
     */
    protected $reason_drop;

    /**
     * @var \DateTime
     */
    protected $date_spam;

    public function __construct()
    {
        $this->date_created = new \DateTime();
        $this->code         = self::genCode();
    }

    /**
     * @return string
     */
    public static function genCode()
    {
        $time = \Orb\Util\Util::baseEncode(time(), 'letters');

        return $time.\Orb\Util\DpStrings::random(30 - strlen($time), Strings::CHARS_ALPHANUM_IU);
    }

    /**
     * @param Ticket $ticket
     * @param array  $to_addresses
     * @param $subject
     * @param $from_address
     *
     * @return string
     */
    public static function insertTicketLog(Ticket $ticket, array $to_addresses, $subject, $from_address)
    {
        $code = self::genCode();

        $from_address = strtolower($from_address);

        $to_addresses = Arrays::func($to_addresses, 'strtolower');
        $to_addresses = array_unique($to_addresses);

        if (!$to_addresses) {
            return;
        }

        $now   = date('Y-m-d H:i:s');
        $batch = [];

        $people_ids = App::getDb()->fetchAllKeyValue('
            SELECT email, person_id
            FROM people_emails
            WHERE email IN (?)
        ', [$to_addresses]);

        foreach ($to_addresses as $to) {
            $batch[] = [
                'code'         => $code,
                'to_address'   => $to,
                'person_id'    => isset($people_ids[$to]) ? $people_ids[$to] : null,
                'from_address' => $from_address,
                'subject'      => $subject,
                'date_created' => $now,
                'ticket_id'    => $ticket->getId(),
            ];
        }

        App::getDb()->batchInsert('sendmail_logs', $batch);

        return $code;
    }

    /**
     * @param TicketMessage $ticket_message
     * @param array         $to_addresses
     * @param $subject
     * @param $from_address
     *
     * @return string
     */
    public static function insertTicketMessageLog(TicketMessage $ticket_message, array $to_addresses, $subject, $from_address)
    {
        $code = self::genCode();

        $from_address = strtolower($from_address);

        $to_addresses = Arrays::func($to_addresses, 'strtolower');
        $to_addresses = array_unique($to_addresses);

        if (!$to_addresses) {
            return;
        }

        $now   = date('Y-m-d H:i:s');
        $batch = [];

        $people_ids = App::getDb()->fetchAllKeyValue('
            SELECT email, person_id
            FROM people_emails
            WHERE email IN (?)
        ', [$to_addresses]);

        foreach ($to_addresses as $to) {
            $batch[] = [
                'code'              => $code,
                'to_address'        => $to,
                'person_id'         => isset($people_ids[$to]) ? $people_ids[$to] : null,
                'from_address'      => $from_address,
                'subject'           => $subject,
                'date_created'      => $now,
                'ticket_id'         => $ticket_message->ticket->getId(),
                'ticket_message_id' => $ticket_message->getId(),
            ];
        }

        App::getDb()->batchInsert('sendmail_logs', $batch);

        return $code;
    }

    /**
     * @param array $to_addresses
     * @param $subject
     * @param $from_address
     *
     * @return string
     */
    public static function insertLog(array $to_addresses, $subject, $from_address)
    {
        $code = self::genCode();

        $from_address = strtolower($from_address);

        $to_addresses = Arrays::func($to_addresses, 'strtolower');
        $to_addresses = array_unique($to_addresses);

        if (!$to_addresses) {
            return;
        }

        $now   = date('Y-m-d H:i:s');
        $batch = [];

        $people_ids = App::getDb()->fetchAllKeyValue('
            SELECT email, person_id
            FROM people_emails
            WHERE email IN (?)
        ', [$to_addresses]);

        foreach ($to_addresses as $to) {
            $batch[] = [
                'code'         => $code,
                'to_address'   => $to,
                'person_id'    => isset($people_ids[$to]) ? $people_ids[$to] : null,
                'from_address' => $from_address,
                'subject'      => $subject,
                'date_created' => $now,
            ];
        }

        App::getDb()->batchInsert('sendmail_logs', $batch);

        return $code;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable([
            'name'              => 'sendmail_logs',
            'uniqueConstraints' => [
                'code' => [
                    'columns' => [
                        'code',
                        'to_address',
                    ],
                ],
            ],
        ]);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_AUTO);
        $metadata->mapField([
            'fieldName'  => 'id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'id',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'code',
            'type'       => 'string',
            'length'     => 30,
            'nullable'   => false,
            'columnName' => 'code',
        ]);
        $metadata->mapField([
            'fieldName'  => 'to_address',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'to_address',
            'uid'        => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'subject',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'subject',
        ]);
        $metadata->mapField([
            'fieldName'  => 'from_address',
            'type'       => 'string',
            'length'     => 255,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'from_address',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_created',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'date_created',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_process',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'date_process',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_deliver',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'date_deliver',
        ]);
        $metadata->mapField([
            'fieldName'  => 'reason_deliver',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'reason_deliver',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_open',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'date_open',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_click',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'date_click',
        ]);
        $metadata->mapField([
            'fieldName'  => 'clicked_urls',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'clicked_urls',
        ]);
        $metadata->mapField([
            'fieldName'  => 'count_open',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'count_open',
        ]);
        $metadata->mapField([
            'fieldName'  => 'count_click',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'count_click',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_defer',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'date_defer',
        ]);
        $metadata->mapField([
            'fieldName'  => 'reason_defer',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'reason_defer',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_bounce',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'date_bounce',
        ]);
        $metadata->mapField([
            'fieldName'  => 'bounce_code',
            'type'       => 'string',
            'length'     => 10,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'bounce_code',
        ]);
        $metadata->mapField([
            'fieldName'  => 'bounce_type',
            'type'       => 'string',
            'length'     => 10,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'bounce_type',
        ]);
        $metadata->mapField([
            'fieldName'  => 'reason_bounce',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'reason_bounce',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_drop',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'date_drop',
        ]);
        $metadata->mapField([
            'fieldName'  => 'reason_drop',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'reason_drop',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_spam',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'date_spam',
        ]);

        $metadata->mapManyToOne([
            'fieldName'    => 'ticket',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Ticket',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'ticket_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'ticket_message',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\TicketMessage',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'ticket_message_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'person',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'person_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
    }
}
