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
use JMS\Serializer\Annotation as JMS;

/**
 * @property int       $id
 * @property string    $sla_status
 * @property \DateTime $warn_date
 * @property \DateTime $fail_date
 * @property bool      $is_completed
 * @property bool      $is_completed_set
 * @property int       $completed_time_taken
 * @property Ticket    $ticket
 * @property Sla       $sla
 *
 * @JMS\ExclusionPolicy("all")
 */
class TicketSla extends DomainObject
{
    const STATUS_OK      = 'ok';
    const STATUS_WARNING = 'warning';
    const STATUS_FAIL    = 'fail';

    /**
     * The unique ID.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id = null;

    /**
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $sla_status = 'ok';

    /**
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime|null
     */
    protected $warn_date;

    /**
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime|null
     */
    protected $fail_date;

    /**
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $is_completed = false;

    /**
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $is_completed_set = false;

    /**
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var null|int
     */
    protected $completed_time_taken = null;

    /**
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Ticket>")
     *
     * @var Ticket
     */
    protected $ticket;

    /**
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Sla>")
     *
     * @var Sla
     */
    protected $sla;

    /**
     * @param Ticket $ticket
     *
     * @return $this
     */
    public function setTicket(Ticket $ticket)
    {
        $this->setModelField('ticket', $ticket);
        $ticket->getTicketSlas()->add($this);

        return $this;
    }

    /**
     * @return Ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    public function setSlaStatus($s)
    {
        $old = $this->sla_status;
        if ($s === $old) {
            return;
        }

        $this->setModelField('sla_status', $s);

        if ($this->ticket) {
            $this->ticket->getStateChangeRecorder()->recordData(
                'ticket_slas_status',
                [
                    'sla'        => $this->sla,
                    'old_status' => $old,
                    'new_status' => $s,
                ]
            );
        }
    }

    /**
     * @param bool           $value
     * @param \DateTime|null $date
     */
    public function setIsCompleted($value, \DateTime $date = null)
    {
        $value = (bool) $value;

        $this->setModelField('is_completed', $value);
        if ($this->is_completed) {
            if ($date === null) {
                $date = new \DateTime();
            }
            if ($date) {
                $this->setModelField(
                    'completed_time_taken',
                    $this->sla->getCalculator()->calculateTimeUntil($this->ticket, $date)
                );
            } else {
                $this->setModelField('completed_time_taken', null);
            }
        } else {
            $this->setModelField('completed_time_taken', null);
        }

        if ($this->ticket) {
            $this->ticket->getStateChangeRecorder()->recordData(
                'ticket_slas_complete',
                [
                    'sla'         => $this->sla,
                    'is_complete' => $value,
                ]
            );
        }
    }

    /**
     * Same as setIsCompleted but the completed status is set forever (unless its overriden with a trigger etc).
     * Usually when status changes, the SLA is re-calculated.
     *
     * @param      $value
     * @param null $date
     */
    public function setIsCompletedSet($value, $date = null)
    {
        $this->setIsCompleted($value, $date);
        if ($value) {
            $this->setModelField('is_completed_set', true);
        } else {
            $this->setModelField('is_completed_set', false);
        }
    }

    /**
     * @return \DateTime|null
     */
    public function getNextTriggerDate()
    {
        $times = [];

        if ($this->sla_status == self::STATUS_OK && $this->warn_date && $this->warn_date->getTimestamp() > time()) {
            $times[] = $this->warn_date->getTimestamp();
        }

        if (in_array(
                $this->sla_status,
                [self::STATUS_OK, self::STATUS_WARNING]
            ) && $this->fail_date && $this->fail_date->getTimestamp() > time()
        ) {
            $times[] = $this->fail_date->getTimestamp();
        }

        if (!$times) {
            return;
        }

        return new \DateTime('@'.min($times));
    }

    /**
     * {@inheritdoc}
     */
    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data = parent::toApiData($primary, $deep, $visited);

        return $data;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
        $metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;
        $metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TicketSla';

        $metadata->setPrimaryTable(
            [
                'name'    => 'ticket_slas',
                'indexes' => [
                    'status_completed_warn_date_idx' => [
                        'columns' => [
                            'sla_status',
                            'is_completed',
                            'warn_date',
                        ],
                    ],
                    'status_completed_fail_date_idx' => [
                        'columns' => [
                            'sla_status',
                            'is_completed',
                            'fail_date',
                        ],
                    ],
                    'completed_id_status' => [
                        'columns' => [
                            'is_completed',
                            'sla_id',
                            'sla_status',
                        ],
                    ],
                ],
                'uniqueConstraints' => [
                    'unique_ticket_sla_idx' => [
                        'columns' => [
                            'ticket_id',
                            'sla_id',
                        ],
                    ],
                ],
            ]
        );

        $metadata->mapField(
            [
                'id'         => true,
                'fieldName'  => 'id',
                'columnName' => 'id',
                'type'       => 'integer',
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'sla_status',
                'columnName' => 'sla_status',
                'type'       => 'string',
                'length'     => 20,
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'warn_date',
                'columnName' => 'warn_date',
                'type'       => 'datetime',
                'nullable'   => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'fail_date',
                'columnName' => 'fail_date',
                'type'       => 'datetime',
                'nullable'   => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'is_completed',
                'columnName' => 'is_completed',
                'type'       => 'boolean',
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'is_completed_set',
                'columnName' => 'is_completed_set',
                'type'       => 'boolean',
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'completed_time_taken',
                'columnName' => 'completed_time_taken',
                'type'       => 'integer',
                'nullable'   => true,
            ]
        );

        $metadata->mapManyToOne(
            [
                'fieldName'    => 'ticket',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Ticket',
                'inversedBy'   => 'ticket_slas',
                'joinColumns'  => [
                    [
                        'name'                 => 'ticket_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                    ],
                ],
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'sla',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Sla',
                'joinColumns'  => [
                    [
                        'name'                 => 'sla_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
    }
}
