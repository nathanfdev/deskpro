<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Ticket charges.
 */
class TicketCharge extends DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * Doctrine clear id for removed entities but we need them sometimes
     * Save here id in preRemove callback.
     *
     * @var int
     */
    protected $id_removed = null;

    /**
     * @var int|null
     */
    protected $charge_time;

    /**
     * @var float|null
     */
    protected $amount;

    /**
     * @var \DateTime
     */
    protected $date_created;

    /**
     * @var \Application\DeskPRO\Entity\Ticket|null
     */
    protected $ticket;

    /**
     * @var \Application\DeskPRO\Entity\Person|null
     */
    protected $person;

    /**
     * @var \Application\DeskPRO\Entity\Organization|null
     */
    protected $organization;

    /**
     * @var \Application\DeskPRO\Entity\Person|null
     */
    protected $agent;

    /**
     * @var ArrayCollection
     */
    protected $custom_data;

    public function __construct()
    {
        $this['date_created'] = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->custom_data    = new ArrayCollection();
    }

    /**
     * Doctrine clear Id for removed entities ($em->remove($entity))
     * $returnRemoved = true - means return Id that was before removal.
     *
     * @param bool $returnRemoved
     *
     * @return int
     */
    public function getId($returnRemoved = false)
    {
        if (!$this->id && $this->id_removed) {
            return $this->id_removed;
        }

        return $this->id;
    }

    /**
     * @return int|null
     */
    public function getChargeTime()
    {
        return $this->charge_time;
    }

    /**
     * @return float|null
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @param Ticket $ticket
     *
     * @return $this
     */
    public function setTicket(Ticket $ticket = null)
    {
        $this->setModelField('ticket', $ticket);

        return $this;
    }

    /**
     * @return Ticket|null
     */
    public function getTicket()
    {
        return $this->ticket;
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
     * @return Person|null
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param Organization $organization
     *
     * @return $this
     */
    public function setOrganization(Organization $organization = null)
    {
        $this->setModelField('organization', $organization);

        return $this;
    }

    /**
     * @return Organization|null
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param Person $agent
     *
     * @return $this
     */
    public function setAgent(Person $agent = null)
    {
        $this->setModelField('agent', $agent);

        return $this;
    }

    /**
     * @return Person|null
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * @return ArrayCollection
     */
    public function getCustomData()
    {
        return $this->custom_data;
    }

    /**
     * Find an existing data record for a field id.
     *
     * @param int $field_id
     *
     * @return CustomDataBilling
     */
    public function getCustomDataForField($field_id)
    {
        if ($field_id instanceof CustomDefBilling) {
            $field_id = $field_id['id'];
        }

        foreach ($this->custom_data as $data) {
            if ($data['field_id'] == $field_id) {
                return $data;
            }
        }

        return;
    }

    /**
     * Reset custom data.
     *
     * @return $this
     */
    public function resetCustomData()
    {
        foreach ($this->custom_data as $data) {
            /* @var $data Entity\CustomDataBilling */
            $this->getStateChangeRecorder()->record('custom_data.'.$data['root_field']['id'], $data, null, true);
        }
        $this->custom_data->clear();

        return $this;
    }

    public function removeCustomDataForField($field)
    {
        $parent_id = null;
        $field_id  = $field['id'];
        if ($field->parent) {
            $parent_id = $field->parent['id'];
        }

        $change = false;
        foreach ($this->custom_data as $data) {
            if ($data['field_id'] == $field_id or $data['field_id'] == $parent_id) {
                $change = true;
                $this->custom_data->removeElement($data);

                if ($parent_id) {
                    $this->getStateChangeRecorder()->record("custom_data.$parent_id", $data, null, true);
                } else {
                    $this->getStateChangeRecorder()->record("custom_data.$field_id", $data, null, true);
                }
            }
        }

        if ($change) {
            $this->_onPropertyChanged('custom_data', null, $this->custom_data);
        }
    }

    /**
     * Set custom field data for a particular field.
     *
     * @param int   $field_id
     * @param       $value_type
     * @param mixed $value
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function setCustomData($field_id, $value_type, $value)
    {
        $custom_data = $this->getCustomDataForField($field_id);
        $is_new      = false;

        if (!$custom_data) {
            if ($value === null) {
                return;
            }

            $is_new = true;

            $field = App::getEntityRepository('DeskPRO:CustomDefBilling')->find($field_id);
            if (!$field) {
                throw new \Exception("Invalid field_id `$field_id`");
            }
            $custom_data          = new CustomDataBilling();
            $custom_data['field'] = $field;
        }

        $field = $custom_data->field;
        if ($field->parent) {
            foreach ($this->custom_data as $d) {
                if ($d->field && $d->field->parent && $d->field->parent['id'] == $field->parent['id']) {
                    $this->custom_data->removeElement($d);
                }
            }
        }

        $this->custom_data->removeElement($custom_data);

        if ($value === null) {
            $this->custom_data->removeElement($custom_data);

            return;
        }

        if ($field->getTypeName() == 'choice') {
        }

        $custom_data[$value_type] = $value;

        if ($is_new) {
            $this->addCustomData($custom_data);
        }

        $this->_onPropertyChanged('custom_data', null, $this->custom_data);

        return $custom_data;
    }

    /**
     * Add a custom data item to this ticket.
     *
     * @param CustomDataBilling $data
     */
    public function addCustomData(CustomDataBilling $data)
    {
        $this->custom_data->add($data);
        $data['ticket_charge'] = $this;

        $field     = $data->field;
        $parent_id = null;
        $field_id  = $field['id'];
        if ($field->parent) {
            $parent_id = $field->parent['id'];
        }

        if ($parent_id) {
            $this->getStateChangeRecorder()->record("custom_data.$parent_id", null, $data, true);
        } else {
            $this->getStateChangeRecorder()->record("custom_data.$field_id", null, $data, true);
        }

        $this->_onPropertyChanged('custom_data', null, $this->custom_data);
    }

    /**
     * Render a custom field.
     *
     * @deprecated
     *
     * @param        $field_id
     * @param string $context
     */
    public function renderCustomField($field_id, $context = 'html')
    {
        $f_def = App::getEntityRepository('DeskPRO:CustomDefBilling')->find($field_id);

        $data_structured = App::getApi('custom_fields.util')->createDataHierarchy($this->custom_data, [$f_def]);

        $value    = !empty($data_structured[$f_def['id']]) ? $data_structured[$f_def['id']] : null;
        $rendered = $value ? $f_def->getHandler()->renderContext($context, $value) : null;

        return $rendered;
    }

    /**
     * Check if this ticket has a custom field.
     *
     * @param $field_id
     *
     * @return bool
     */
    public function hasCustomField($field_id)
    {
        foreach ($this->custom_data as $data) {
            if ($data->field['id'] == $field_id) {
                return true;
            }
        }

        foreach ($this->custom_data as $data) {
            if ($data->field->parent and $data->field->parent['id'] == $field_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets a display array for a specific field.
     *
     * @param $field_id
     *
     * @return array|mixed|null
     */
    public function getCustomFieldDisplayArray($field_id)
    {
        $data = $this->getCustomDataForField($field_id);
        if (!$data) {
            return;
        }

        $field_defs      = App::getApi('custom_fields.billing')->getEnabledFields();
        $data_structured = App::getApi('custom_fields.util')->createDataHierarchy([$data], $field_defs);

        $custom_fields = App::getApi('custom_fields.billing')->getFieldsDisplayArray(
            $field_defs,
            $data_structured
        );

        $custom_fields = array_pop($custom_fields);

        return $custom_fields;
    }

    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data = parent::toApiData($primary, $deep, $visited);

        // Render custom fields to text values
        $field_manager = App::getContainer()->getSystemService('billing_fields_manager');
        $field_manager->addApiData($this, $data);

        if ($first = $this->custom_data->first()) {
            if ('Comment' === $first->field['title']) {
                $data['comment'] = $first['input'];
            }
        }

        return $data;
    }

    /**
     * Lifecycle Callback.
     */
    public function onPreRemove()
    {
        $this->id_removed = $this->id;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TicketCharge';
        $metadata->setPrimaryTable(['name' => 'ticket_charges']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField(
            [
                'fieldName'  => 'id',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'id',
                'id'         => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'charge_time',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'charge_time',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'amount',
                'type'       => 'decimal',
                'precision'  => 10,
                'scale'      => 2,
                'nullable'   => true,
                'columnName' => 'amount',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_created',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'date_created',
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'ticket',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Ticket',
                'mappedBy'     => null,
                'inversedBy'   => 'charges',
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'ticket_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->mapManyToOne(
            [
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
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'organization',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Organization',
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'organization_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'agent',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'agent_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );

        $metadata->mapOneToMany(
            [
                'fieldName'    => 'custom_data',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\CustomDataBilling',
                'cascade'      => [
                    'remove',
                    'persist',
                    'merge',
                    'detach',
                ],
                'mappedBy'      => 'ticket_charge',
                'orphanRemoval' => true,
                'dpApi'         => true,
            ]
        );

        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->addLifecycleCallback('onPreRemove', 'preRemove');
    }
}
