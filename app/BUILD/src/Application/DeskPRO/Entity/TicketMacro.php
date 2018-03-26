<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Tickets\TicketActions\ActionsCollection;
use Application\DeskPRO\Tickets\TicketActions\ActionsFactory;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;
use Orb\Util\Arrays;
use Orb\Util\Strings;

/**
 * Ticket macros.
 *
 * @JMS\ExclusionPolicy("all")
 */
class TicketMacro extends DomainObject
{
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
     * The person - owner of the macro.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person = null;

    /**
     * Department which can use the macro.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Department>")
     *
     * @var \Application\DeskPRO\Entity\Department
     */
    protected $department;

    /**
     * Title of the macro.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $title;

    /**
     * Is macro enabled?
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $is_enabled = true;

    /**
     * Is macro global?
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $is_global = false;

    /**
     * Macro actions.
     *
     * @JMS\Expose()
     * @JMS\Type("array")
     *
     * @var array
     */
    protected $actions = [];

    /**
     * @var ActionsCollection
     */
    protected $_actions_coll;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param Person $person
     *
     * @return $this
     */
    public function setPerson(Person $person = null)
    {
        $this->setModelField('person', $person);

        return $this;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @return Department
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @param Department $department
     *
     * @return $this
     */
    public function setDepartment(Department $department = null)
    {
        $this->setModelField('department', $department);

        return $this;
    }

    /**
     * @param bool $is_global
     *
     * @return $this
     */
    public function setIsGlobal($is_global)
    {
        $this->setModelField('is_global', $is_global);

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsGlobal()
    {
        return $this->is_global;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->is_enabled;
    }

    /**
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @return array
     */
    public function getActionsArrayDesc()
    {
        $ret = [];

        foreach ($this->actions as $info) {
            if (!isset($info['rule_type'])) {
                continue;
            }

            $type = $info['rule_type'];
            unset($info['rule_type']);

            if (count($info) == 1) {
                $info = array_pop($info);
            }

            $ret[$type] = $info;
        }

        return $ret;
    }

    /**
     * Get a simple array of actions used to pass back to views to update
     * UI.
     *
     * @return \Application\DeskPRO\Tickets\TicketActions\ActionsCollection
     */
    public function getActionsCollection()
    {
        if ($this->_actions_coll) {
            return $this->_actions_coll;
        }

        $factory    = new ActionsFactory();
        $collection = new ActionsCollection();

        foreach ($this->actions as $action_info) {
            $action = $factory->createFromInfo($action_info);
            if ($action) {
                $collection->add($action);
            }
        }

        $this->_actions_coll = $collection;

        return $this->_actions_coll;
    }

    /**
     * Get macro summary.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("array<string>")
     *
     * @return array
     */
    public function getSummary()
    {
        $descriptions = [];
        foreach ($this->getActionDescriptions(false) as $description) {
            $descriptions[] = Strings::html2Text($description);
        }

        return $descriptions;
    }

    /**
     * @param bool $as_html
     *
     * @return array
     */
    public function getActionDescriptions($as_html = true)
    {
        return $this->getActionsCollection()->getDescriptions($as_html);
    }

    /**
     * @param Ticket      $ticket
     * @param Person|null $person_context
     *
     * @throws \Exception
     */
    public function performOnTicket(Ticket $ticket, Entity\Person $person_context = null)
    {
        $collection = $this->getActionsCollection();
        if (!$person_context) {
            $person_context = App::getCurrentPerson();
        }

        $collection->apply($ticket->getTicketLogger(), $ticket, $person_context);
    }

    /**
     * @param Person $person
     *
     * @return bool
     */
    public function performOnPerson(Entity\Person $person)
    {
        $did_change = false;

        foreach ($this->actions as $action) {
            $term    = $action['type'];
            $term_id = null;

            // $term of people_field[12] becomes $term=people_field, $term_id=12
            $m = null;
            if (preg_match('#^(.*?)\[(.*?)\]$#', $term, $m)) {
                $term    = $m[1];
                $term_id = $m[2];
            }

            switch ($term) {
                case 'people_field':

                    $value = $action;
                    unset($value['rule_type'], $value['op'], $value['renderable_value']);

                    $field = App::getEntityRepository('DeskPRO:CustomDefPerson')->find($term_id);
                    if (!$field) {
                        break;
                    }

                    foreach ($field->getHandler()->getDataFromForm($value) as $info) {
                        $person->setCustomDataField($info[0], $info[1], $info[2]);
                    }

                    $did_change = true;
                    break;

                case 'person_organization_id':
                    $person['organization_id'] = $action['person_organization_id'];
                    $did_change                = true;
                    break;
            }
        }

        if ($did_change) {
            App::getOrm()->persist($person);
        }

        return $did_change;
    }

    /**
     * Gets parts of the title using the "->" as a separtor.
     *
     * "Sales -> FooBar" returns ["Sales", "FooBar"]
     *
     * @return array
     */
    public function getTitleParts()
    {
        return Arrays::removeEmptyString(array_map('trim', explode('->', $this->title)));
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TicketMacro';
        $metadata->setPrimaryTable(['name' => 'ticket_macros']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
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
            'fieldName'  => 'title',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'title',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_enabled',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_enabled',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_global',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_global',
        ]);
        $metadata->mapField([
            'fieldName'  => 'actions',
            'type'       => 'array',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'actions',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
            'fieldName'    => 'person',
            'targetEntity' => Person::class,
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
            'dpApi' => true,
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'department',
            'targetEntity' => Department::class,
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'department_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                    'columnDefinition'     => null,
                ],
            ],
            'dpApi' => true,
        ]);
    }
}
