<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * @property int       $id
 * @property string    $ref
 * @property int       $language_id
 * @property int       $department_id
 * @property int       $category_id
 * @property int       $workflow_id
 * @property int       $priority_id
 * @property int       $product_id
 * @property int       $person_id
 * @property int       $person_email_id
 * @property int       $agent_id
 * @property int       $agent_team_id
 * @property int       $organization_id
 * @property string    $sent_to_address
 * @property string    $creation_system
 * @property string    $creation_system_option
 * @property string    $status
 * @property bool      $is_hold
 * @property int       $urgency
 * @property int       $feedback_rating
 * @property \DateTime $date_feedback_rating
 * @property \DateTime $date_created
 * @property \DateTime $date_resolved
 * @property \DateTime $date_on_hold
 * @property \DateTime $date_first_agent_assign
 * @property \DateTime $date_first_agent_reply
 * @property \DateTime $date_last_agent_reply
 * @property \DateTime $date_last_user_reply
 * @property \DateTime $date_agent_waiting
 * @property \DateTime $date_user_waiting
 * @property \DateTime $date_status
 * @property int       $total_user_waiting
 * @property int       $total_to_first_reply
 * @property string    $subject
 * @property string    $original_subject
 */
class TicketSearchActive extends DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var string
     */
    protected $ref = null;

    /**
     * @var int
     */
    protected $language_id = null;

    /**
     * @var int
     */
    protected $brand_id = null;

    /**
     * @var int
     */
    protected $department_id = null;

    /**
     * @var int
     */
    protected $category_id = null;

    /**
     * @var int
     */
    protected $workflow_id = null;

    /**
     * @var int
     */
    protected $priority_id = null;

    /**
     * @var int
     */
    protected $product_id = null;

    /**
     * @var int
     */
    protected $person_id = null;

    /**
     * @var int
     */
    protected $person_email_id = null;

    /**
     * @var int
     */
    protected $agent_id = null;

    /**
     * @var int
     */
    protected $agent_team_id = null;

    /**
     * @var int
     */
    protected $organization_id = null;

    /**
     * @var int
     */
    protected $email_account_id = null;

    /**
     * @var string
     */
    protected $sent_to_address;

    /**
     * @var string
     */
    protected $creation_system;

    /**
     * Optional information about the creation system. For example, source URL the ticket came from.
     *
     * @var string
     */
    protected $creation_system_option = '';

    /**
     * @var string
     */
    protected $status;

    /**
     * Is the ticket on hold?
     *
     * @var bool
     */
    protected $is_hold = false;

    /**
     * @var int
     */
    protected $urgency = 1;

    /**
     * @var int
     */
    protected $feedback_rating = null;

    /**
     * @var \DateTime
     */
    protected $date_feedback_rating = null;

    /**
     * @var \DateTime
     */
    protected $date_created;

    /**
     * @var \DateTime
     */
    protected $date_resolved;

    /**
     * @var \DateTime
     */
    protected $date_on_hold;

    /**
     * @var \DateTime
     */
    protected $date_first_agent_assign = null;

    /**
     * @var \DateTime
     */
    protected $date_first_agent_reply = null;

    /**
     * @var \DateTime
     */
    protected $date_last_agent_reply = null;

    /**
     * @var \DateTime
     */
    protected $date_last_user_reply = null;

    /**
     * @var \DateTime
     */
    protected $date_agent_waiting = null;

    /**
     * @var \DateTime
     */
    protected $date_user_waiting = null;

    /**
     * @var \DateTime
     */
    protected $date_status = null;

    /**
     * @var int
     */
    protected $total_user_waiting = 0;

    /**
     * @var int
     */
    protected $total_to_first_reply = 0;

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var string
     */
    protected $original_subject = '';

    /**
     * @return array
     */
    public static function getFieldNames()
    {
        return [
            'id', 'ref', 'language_id', 'brand_id', 'department_id', 'category_id',
            'workflow_id', 'priority_id', 'product_id', 'person_id', 'person_email_id',
            'agent_id', 'agent_team_id', 'organization_id', 'email_account_id', 'creation_system', 'creation_system_option',
            'status', 'is_hold', 'urgency', 'feedback_rating', 'date_feedback_rating',
            'date_created', 'date_resolved', 'date_on_hold', 'date_first_agent_assign', 'date_first_agent_reply',
            'date_last_agent_reply', 'date_last_user_reply', 'date_agent_waiting', 'date_user_waiting', 'date_status',
            'total_user_waiting', 'total_to_first_reply',
            'subject', 'original_subject',
        ];
    }

    /**
     * @return string
     */
    public static function getFieldNamesAsSqlString()
    {
        static $cols;

        if ($cols === null) {
            $cols = '`'.implode('`, `', self::getFieldNames()).'`';
        }

        return $cols;
    }

    /**
     * Given a ticket, get a raw database array we can copy into the search active table.
     *
     * @param Ticket $ticket
     *
     * @return array
     */
    public static function copyTicketDbArray(Ticket $ticket)
    {
        $db_array = [];

        foreach (self::getFieldNames() as $field) {
            $prop = $field;
            if (substr($prop, -3) == '_id') {
                $prop = substr($prop, 0, -3);
                $val  = $ticket->$prop;
            } else {
                $val = $ticket->$field;
            }

            if (is_object($val)) {
                if ($val instanceof \DateTime) {
                    $db_array[$field] = $val->format('Y-m-d H:i:s');
                } else {
                    $db_array[$field] = $val->id;
                }
            } else {
                $db_array[$field] = $val;
            }
        }

        return $db_array;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->inheritanceType      = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
        $metadata->changeTrackingPolicy = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
        $metadata->generatorType        = ClassMetadataInfo::GENERATOR_TYPE_NONE;
        $metadata->setPrimaryTable([
            'name'    => 'tickets_search_active',
            'indexes' => [
                'date_created_idx' => ['columns' => ['date_created']],
                'status_idx'       => ['columns' => ['status']],
                'person_idx'       => ['columns' => ['person_id']],
                'agent_idx'        => ['columns' => ['agent_id']],
                'ref_idx'          => ['columns' => ['ref']],
            ],
        ]);

        $metadata->mapField([
            'columnName' => 'id',
            'fieldName'  => 'id',
            'type'       => 'integer',
            'id'         => true,
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'ref',
            'fieldName'  => 'ref',
            'type'       => 'string',
            'length'     => 100,
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'language_id',
            'fieldName'  => 'language_id',
            'type'       => 'integer',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'columnName' => 'brand_id',
            'fieldName'  => 'brand_id',
            'type'       => 'integer',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'columnName' => 'department_id',
            'fieldName'  => 'department_id',
            'type'       => 'integer',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'columnName' => 'category_id',
            'fieldName'  => 'category_id',
            'type'       => 'integer',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'columnName' => 'workflow_id',
            'fieldName'  => 'workflow_id',
            'type'       => 'integer',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'columnName' => 'priority_id',
            'fieldName'  => 'priority_id',
            'type'       => 'integer',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'columnName' => 'product_id',
            'fieldName'  => 'product_id',
            'type'       => 'integer',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'columnName' => 'person_id',
            'fieldName'  => 'person_id',
            'type'       => 'integer',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'columnName' => 'person_email_id',
            'fieldName'  => 'person_email_id',
            'type'       => 'integer',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'columnName' => 'agent_id',
            'fieldName'  => 'agent_id',
            'type'       => 'integer',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'columnName' => 'agent_team_id',
            'fieldName'  => 'agent_team_id',
            'type'       => 'integer',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'columnName' => 'organization_id',
            'fieldName'  => 'organization_id',
            'type'       => 'integer',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'columnName' => 'email_account_id',
            'fieldName'  => 'email_account_id',
            'type'       => 'integer',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'columnName' => 'sent_to_address',
            'fieldName'  => 'sent_to_address',
            'type'       => 'string',
            'length'     => 200,
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'creation_system',
            'fieldName'  => 'creation_system',
            'type'       => 'string',
            'length'     => 100,
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'creation_system_option',
            'fieldName'  => 'creation_system_option',
            'type'       => 'string',
            'length'     => 1000,
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'fieldName'  => 'status',
            'columnName' => 'status',
            'type'       => 'string',
            'length'     => 30,
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_hold',
            'columnName' => 'is_hold',
            'type'       => 'boolean',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'fieldName'  => 'urgency',
            'columnName' => 'urgency',
            'type'       => 'integer',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'fieldName'  => 'feedback_rating',
            'columnName' => 'feedback_rating',
            'type'       => 'integer',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_feedback_rating',
            'columnName' => 'date_feedback_rating',
            'type'       => 'datetime',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_created',
            'columnName' => 'date_created',
            'type'       => 'datetime',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_resolved',
            'columnName' => 'date_resolved',
            'type'       => 'datetime',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_on_hold',
            'columnName' => 'date_on_hold',
            'type'       => 'datetime',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_first_agent_assign',
            'columnName' => 'date_first_agent_assign',
            'type'       => 'datetime',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_first_agent_reply',
            'columnName' => 'date_first_agent_reply',
            'type'       => 'datetime',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_last_agent_reply',
            'columnName' => 'date_last_agent_reply',
            'type'       => 'datetime',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_last_user_reply',
            'columnName' => 'date_last_user_reply',
            'type'       => 'datetime',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_agent_waiting',
            'columnName' => 'date_agent_waiting',
            'type'       => 'datetime',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_user_waiting',
            'columnName' => 'date_user_waiting',
            'type'       => 'datetime',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_status',
            'columnName' => 'date_status',
            'type'       => 'datetime',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'fieldName'  => 'total_user_waiting',
            'columnName' => 'total_user_waiting',
            'type'       => 'integer',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'fieldName'  => 'total_to_first_reply',
            'columnName' => 'total_to_first_reply',
            'type'       => 'integer',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'fieldName'  => 'subject',
            'columnName' => 'subject',
            'type'       => 'string',
            'length'     => 255,
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'fieldName'  => 'original_subject',
            'columnName' => 'original_subject',
            'type'       => 'string',
            'length'     => 255,
            'nullable'   => false,
        ]);
    }
}
