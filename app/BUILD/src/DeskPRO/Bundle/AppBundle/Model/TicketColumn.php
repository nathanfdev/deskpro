<?php

namespace DeskPRO\Bundle\AppBundle\Model;

/**
 * Class TicketColumn.
 */
class TicketColumn
{
    const TYPE_DEPARTMENT_SUBJECT = 'department_subject';
    const TYPE_DEPARTMENT         = 'department';
    const TYPE_SUBJECT            = 'subject';
    const TYPE_USER               = 'user';
    const TYPE_AGENT              = 'agent';
    const TYPE_DATE_CREATED       = 'date_created';
    const TYPE_DATE_ACTIVITY      = 'date_activity';
    const TYPE_DATE_USER          = 'date_user';
    const TYPE_DATE_AGENT         = 'date_agent';
    const TYPE_PROPERTY           = 'ticket_property'; // refers to a direct mapping between column ID -> ticket view property ID

    public static $column_types = [
        self::TYPE_DEPARTMENT_SUBJECT,
        self::TYPE_SUBJECT,
        self::TYPE_DEPARTMENT,
        self::TYPE_USER,
        self::TYPE_AGENT,
        self::TYPE_DATE_CREATED,
        self::TYPE_DATE_ACTIVITY,
        self::TYPE_DATE_USER,
        self::TYPE_DATE_AGENT,
        self::TYPE_PROPERTY,
    ];

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $widgetType;

    /**
     * Constructor.
     *
     * @param string $id
     * @param string $label
     * @param string $type
     * @param string $widgetType
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($id, $label, $type, $widgetType)
    {
        if (!$id) {
            throw new \InvalidArgumentException('cannot create a TicketColumn with no ID');
        }

        if (!$label) {
            throw new \InvalidArgumentException('cannot create a TicketColumn with no label');
        }

        if (!in_array($type, self::$column_types)) {
            throw new \InvalidArgumentException(
                sprintf('could not make a TicketColumn with invalid type: %s', $type)
            );
        }

        $this->id         = $id;
        $this->label      = $label;
        $this->type       = $type;
        $this->widgetType = $widgetType;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getWidgetType()
    {
        return $this->widgetType;
    }
}
