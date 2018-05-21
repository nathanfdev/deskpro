<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Tickets\Actions\ModStopTriggers;
use Application\DeskPRO\Tickets\Actions\SetDeleted;
use Application\DeskPRO\Tickets\Triggers\TriggerActions;
use Application\DeskPRO\Tickets\Triggers\TriggerTerms;
use DeskPRO\Bundle\AppBundle\Entity\ObjectAlias\TicketTriggerAlias;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;
use Orb\Util\Arrays;

/**
 * @property int $id
 * @property Department $department
 * @property EmailAccount $email_account
 * @property string $title
 * @property bool $is_enabled
 * @property string $event_trigger
 * @property array $event_flags
 * @property array $by_agent_mode
 * @property array $by_user_mode
 * @property array $by_app_mode
 * @property \Application\DeskPRO\Tickets\Triggers\TriggerTerms $terms
 * @property \Application\DeskPRO\Tickets\Triggers\TriggerActions $actions
 * @property int $run_order
 */
class TicketTrigger extends DomainObject
{
    const EVENT_TYPE_NEWTICKET = 'newticket';
    const EVENT_TYPE_NEWREPLY  = 'newreply';
    const EVENT_TYPE_UPDATE    = 'update';
    const EVENT_TYPE_WEBHOOK   = 'webhook';

    /**
     * Flag used on 'update' triggers which specifies if they
     * should run on newreplies as well (when there were non-reply changes such as status etc).
     */
    const EVENT_FLAG_RUN_NEWREPLY = 'run_newreply';

    const MODE_WEB    = 'web';
    const MODE_PORTAL = 'portal';
    const MODE_WIDGET = 'widget';
    const MODE_FORM   = 'form';
    const MODE_EMAIL  = 'email';
    const MODE_API    = 'api';
    const MODE_MOBILE = 'mobile';

    /**
     * The unique ID.
     *
     * @var int
     * @JMS\Expose()
     * @JMS\Type("integer")
     */
    protected $id = null;

    /**
     * @var Department
     */
    protected $department;

    /**
     * @var EmailAccount
     */
    protected $email_account;

    /**
     * @var string
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    protected $title = '';

    /**
     * @var bool
     */
    protected $is_enabled = true;

    /**
     * @var bool
     */
    protected $is_hidden = false;

    /**
     * @var bool
     */
    protected $is_editable = true;

    /**
     * @var string
     */
    protected $sys_name = null;

    /**
     * @var string
     */
    protected $event_trigger;

    /**
     * @var array
     */
    protected $event_flags = [];

    /**
     * @var array
     */
    protected $by_agent_mode = [];

    /**
     * @var array
     */
    protected $by_user_mode = [];

    /**
     * @var array
     */
    protected $by_app_mode = [];

    /**
     * @var \Application\DeskPRO\Tickets\Triggers\TriggerTerms
     */
    protected $terms;

    /**
     * @var \Application\DeskPRO\Tickets\Triggers\TriggerActions
     */
    protected $actions;

    protected $_has_stop_trigger_action;

    protected $_has_delete_ticket_action;

    /**
     * @var int
     */
    protected $run_order = 0;

    /**
     * Aliases for this field.
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $aliases = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->terms   = new TriggerTerms();
        $this->actions = new TriggerActions();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param array $modes
     */
    public function setByAgentMode($modes)
    {
        if (!$modes) {
            $this->setModelField('by_agent_mode', []);
        } else {
            if (!is_array($modes)) {
                $modes = explode(',', $modes);
            }

            $modes = Arrays::func($modes, 'trim');
            $modes = Arrays::func($modes, 'strtolower');
            sort($modes, \SORT_STRING);
            $this->setModelField('by_agent_mode', $modes);
        }
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->setModelField('title', $title);

        return $this;
    }

    /**
     * @return string
     */
    public function getSysName()
    {
        return $this->sys_name;
    }

    /**
     * @param string $sys_name
     *
     * @return $this
     */
    public function setSysName($sys_name)
    {
        $this->setModelField('sys_name', $sys_name);

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->is_enabled;
    }

    /**
     * @param bool $is_enabled
     *
     * @return $this
     */
    public function setIsEnabled($is_enabled)
    {
        $this->setModelField('is_enabled', $is_enabled);

        return $this;
    }

    /**
     * @return int
     */
    public function getRunOrder()
    {
        return $this->run_order;
    }

    /**
     * @param int $run_order
     *
     * @return $this
     */
    public function setRunOrder($run_order)
    {
        $this->setModelField('run_order', $run_order);

        return $this;
    }

    /**
     * @return string
     */
    public function getEventTrigger()
    {
        return $this->event_trigger;
    }

    /**
     * @param string $event_trigger
     *
     * @return $this
     */
    public function setEventTrigger($event_trigger)
    {
        $this->setModelField('event_trigger', $event_trigger);

        return $this;
    }

    /**
     * @param array $modes
     */
    public function setByUserMode($modes)
    {
        if (!$modes) {
            $this->setModelField('by_user_mode', []);
        } else {
            if (!is_array($modes)) {
                $modes = explode(',', $modes);
            }

            $modes = Arrays::func($modes, 'trim');
            $modes = Arrays::func($modes, 'strtolower');
            sort($modes, \SORT_STRING);
            $this->setModelField('by_user_mode', $modes);
        }
    }

    /**
     * @param array $modes
     */
    public function setByAppMode($modes)
    {
        if (!$modes) {
            $this->setModelField('by_app_mode', []);
        } else {
            if (!is_array($modes)) {
                $modes = explode(',', $modes);
            }

            $modes = Arrays::func($modes, 'trim');
            $modes = Arrays::func($modes, 'strtolower');
            sort($modes, \SORT_STRING);
            $this->setModelField('by_app_mode', $modes);
        }
    }

    /**
     * @param TriggerActions $actions
     */
    public function setActions($actions)
    {
        $this->setModelField('actions', $actions);
    }

    /**
     * @param string $flag
     *
     * @return bool
     */
    public function hasEventFlag($flag)
    {
        return in_array($flag, $this->event_flags);
    }

    /**
     * @param string $flag
     */
    public function addEventFlag($flag)
    {
        if (!in_array($flag, $this->event_flags)) {
            $flags   = $this->event_flags;
            $flags[] = $flag;
            $this->setModelField('event_flags', $flags);
        }
    }

    /**
     * @param string $flag
     */
    public function removeEventFlag($flag)
    {
        if (($k = array_search($flag, $this->event_flags, true)) !== false) {
            $flags = $this->event_flags;
            unset($flags[$k]);
            $this->setModelField('event_flags', $flags);
        }
    }

    protected function processActions()
    {
        if (!$this->actions) {
            return;
        }

        if (null !== $this->_has_delete_ticket_action && null !== $this->_has_stop_trigger_action) {
            return;
        }

        foreach ($this->actions as $a) {
            if ($a instanceof ModStopTriggers) {
                $this->_has_stop_trigger_action = true;
            }
            if ($a instanceof SetDeleted) {
                $this->_has_delete_ticket_action = true;
            }

            if (null !== $this->_has_delete_ticket_action && null !== $this->_has_stop_trigger_action) {
                break;
            }
        }

        $this->_has_stop_trigger_action  = (bool) $this->_has_stop_trigger_action;
        $this->_has_delete_ticket_action = (bool) $this->_has_delete_ticket_action;
    }

    /**
     * @return bool
     */
    public function hasStopTriggersAction()
    {
        $this->processActions();

        return $this->_has_stop_trigger_action;
    }

    public function hasDeleteTicketAction()
    {
        $this->processActions();

        return $this->_has_delete_ticket_action;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|TicketTriggerAlias[]|null
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * {@inheritdoc}
     */
    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data                             = parent::toApiData($primary, $deep, $visited);
        $data['department']               = $this->department ? ['id' => $this->department->id, 'title' => $this->department->title, 'title_full' => $this->department->getFullTitle()] : null;
        $data['email_account']            = $this->email_account ? ['id' => $this->email_account->id, 'address' => $this->email_account->address] : null;
        $data['by_agent_mode']            = $this->by_agent_mode;
        $data['by_user_mode']             = $this->by_user_mode;
        $data['by_app_mode']              = $this->by_app_mode;
        $data['terms']                    = $this->terms->exportToArray();
        $data['actions']                  = $this->actions->exportToArray();
        $data['has_stop_triggers_action'] = $this->hasStopTriggersAction();
        $data['has_delete_ticket_action'] = $this->hasDeleteTicketAction();
        $data['sys_name']                 = $this->sys_name;
        $data['aliases']                  = $this->getAliases();

        return $data;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TicketTrigger';
        $metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
        $metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;

        $metadata->setPrimaryTable([
            'name' => 'ticket_triggers',
        ]);

        $metadata->mapField([
            'id'         => true,
            'columnName' => 'id',
            'fieldName'  => 'id',
            'type'       => 'integer',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'title',
            'fieldName'  => 'title',
            'type'       => 'string',
            'length'     => 255,
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'event_trigger',
            'fieldName'  => 'event_trigger',
            'type'       => 'string',
            'length'     => 50,
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'event_flags',
            'fieldName'  => 'event_flags',
            'type'       => 'simple_array',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'columnName' => 'by_agent_mode',
            'fieldName'  => 'by_agent_mode',
            'type'       => 'simple_array',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'columnName' => 'by_user_mode',
            'fieldName'  => 'by_user_mode',
            'type'       => 'simple_array',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'columnName' => 'by_app_mode',
            'fieldName'  => 'by_app_mode',
            'type'       => 'simple_array',
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'columnName' => 'is_enabled',
            'fieldName'  => 'is_enabled',
            'type'       => 'boolean',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'is_hidden',
            'fieldName'  => 'is_hidden',
            'type'       => 'boolean',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'is_editable',
            'fieldName'  => 'is_editable',
            'type'       => 'boolean',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'sys_name',
            'fieldName'  => 'sys_name',
            'type'       => 'string',
            'length'     => 150,
            'nullable'   => true,
        ]);
        $metadata->mapField([
            'columnName' => 'terms',
            'fieldName'  => 'terms',
            'type'       => 'dp_json_obj',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'actions',
            'fieldName'  => 'actions',
            'type'       => 'dp_json_obj',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'run_order',
            'fieldName'  => 'run_order',
            'type'       => 'integer',
            'nullable'   => false,
        ]);

        $metadata->mapManyToOne([
            'fieldName'    => 'department',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Department',
            'joinColumns'  => [
                [
                    'name'                 => 'department_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'CASCADE',
                ],
            ],
        ]);
        $metadata->mapOneToMany([
            'fieldName'    => 'aliases',
            'targetEntity' => TicketTriggerAlias::class,
            'cascade'      => [
                0 => 'remove',
                1 => 'persist',
                3 => 'merge',
            ],
            'mappedBy'      => 'object',
            'orphanRemoval' => true,
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'email_account',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\EmailAccount',
            'joinColumns'  => [
                [
                    'name'                 => 'email_account_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'CASCADE',
                ],
            ],
        ]);
    }
}
