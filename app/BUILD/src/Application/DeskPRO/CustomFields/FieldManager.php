<?php

namespace Application\DeskPRO\CustomFields;

use Application\DeskPRO\CustomFields\Handler\HandlerAbstract;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\CustomDataAbstract;
use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\ObjectAlias;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\PersistentCollection;

/**
 * The custom field manager handles fetching custom fields, rendering them
 * and saving them.
 *
 * == Terms ==
 * - `field` or `field_def` is a field definition (CustomDefAbstract).
 *   A field can have children (such as a select box).
 * - `object` is the object that a field is attached to (Ticket, Person, Organization)
 * - `custom_data` is a flat array on an object that stores the data for a field (CustomDataAbstract). It's flat
 *   because Doctrine/database doesn't care about hierarchy.
 * - `field_data` is a re-structured array based off of `custom_data` that has the proper hierarchy defined by the `field_def`s
 * - `display_array` takes a `field_def` and `field_data` to produce an array that has data that can be rendered in a template as
 *   a value or a form.
 */
class FieldManager
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Orb\Util\OptionsArray
     */
    protected $options;

    /**
     * Array of top-level fields for the current interface.
     *
     * @var array
     */
    protected $fields = null;

    /**
     * Array of top-level fields.
     *
     * @var array
     */
    protected $real_fields = null;

    /**
     * @var array
     */
    protected $field_to_children = [];

    /**
     * Array of all fields for the current interface.
     * E.g., if this is the user interface, then agent-only fields aren't included here.
     *
     * @var array
     */
    protected $all_fields = null;

    /**
     * Array of all enabled fields, including ones disabled for the current interface.
     *
     * @var array
     */
    protected $real_all_fields = null;

    /**
     * person context.
     *
     * @var Person
     */
    protected $person;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em, array $options)
    {
        $this->em = $em;
        $this->db = $em->getConnection();

        $this->options = new \Orb\Util\CheckedOptionsArray($options);
        $this->options->ensureRequired([
            'entity_class',
            'entity_name',
            'data_entity_name',
            'data_entity_class',
        ]);

        $this->options->setArrayDefault([
            'custom_data_property' => 'custom_data',
        ]);

        $this->init();
    }

    public function setContextPerson(Person $person)
    {
        $this->person = $person;
    }

    /**
     * @return string
     */
    public function getEntityClass()
    {
        return $this->options->get('entity_class');
    }

    /**
     * @return string
     */
    public function getEntityName()
    {
        return $this->options->get('entity_name');
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->getFields());
    }

    /**
     * Creates a new field def entity. This entity will be unmanaged by this
     * manager. You will need to persist, flush and then reset this object to have the new field added
     * to this manager.
     *
     * @return CustomDefAbstract
     */
    public function createNewDefEntity()
    {
        $class = $this->options->get('entity_class');
        $obj   = new $class();

        return $obj;
    }

    protected function init()
    {
    }

    /**
     * Get a collection of all top-level (parent) fields.
     *
     * @return array
     */
    public function getFields()
    {
        if ($this->fields === null) {
            $this->fields          = [];
            $this->all_fields      = [];
            $this->real_fields     = [];
            $this->real_all_fields = [];

            if ($this->options->get('disabled')) {
                return $this->fields;
            }

            /** @var \Application\DeskPRO\Entity\CustomDefAbstract[] $all_fields */
            $all_fields = $this->em->getRepository($this->options->get('entity_name'))->getEnabledFields();

            foreach ($all_fields as $f) {
                $this->real_all_fields[$f->getId()] = $f;

                if (!$f->getParentId()) {
                    $this->real_fields[$f->getId()] = $f;
                }

                if (defined('DP_INTERFACE') && DP_INTERFACE == 'user') {
                    if (!$f->is_agent_field && $f->is_user_enabled) {
                        $this->all_fields[$f->getId()] = $f;
                        if (!$f->getParentId()) {
                            $this->fields[$f->getId()] = $f;
                        }
                    }
                } else {
                    $this->all_fields[$f->getId()] = $f;
                    if (!$f->getParentId()) {
                        $this->fields[$f->getId()] = $f;
                    }
                }

                if ($p = $f->getParentId()) {
                    if (!isset($this->field_to_children[$p])) {
                        $this->field_to_children[$p] = [];
                    }
                    $this->field_to_children[$p][$f->getId()] = $f;
                }
            }
        }

        return $this->fields;
    }

    /**
     * Get all defined fields, even ones that are not enabled for the current interface.
     *
     * @return \Application\DeskPRO\Entity\CustomDefAbstract[]
     */
    public function getDefinedFields()
    {
        $this->getFields();

        return $this->real_fields;
    }

    /**
     * Get all fields.
     *
     * @return array
     */
    public function getAllFields()
    {
        $this->getFields();

        return $this->all_fields;
    }

    /**
     * Get a named system field.
     *
     * @param string $sys_name
     *
     * @return mixed
     */
    public function getSystemField($sys_name)
    {
        foreach ($this->getFields() as $field) {
            if (isset($field['sys_name']) && $field['sys_name'] == $sys_name) {
                return $field;
            }
        }

        return;
    }

    /**
     * @param $field_def
     *
     * @return array
     */
    public function getFieldChildren($field_def)
    {
        $this->getFields();
        if (!isset($this->field_to_children[$field_def->getId()])) {
            return [];
        }

        return $this->field_to_children[$field_def->getId()];
    }

    /**
     * Get a field from an ID.
     *
     * @param $field_id
     *
     * @return \Application\DeskPRO\Entity\CustomDefAbstract|null
     */
    public function getFieldFromId($field_id)
    {
        $this->getFields();

        // lookup by id
        if (isset($this->fields[$field_id])) {
            return $this->fields[$field_id];
        }

        // lookup by alias
        /** @var CustomDefAbstract $field */
        foreach ($this->fields as $field) {
            foreach ($field->getAliases() as $alias) {
                if ($alias->getQualifiedName() === $field_id) {
                    return $field;
                }
            }
        }

        return;
    }

    /**
     * Get a display array for rendering a field.
     *
     * @param array $field_data  An array of structured data from the database
     * @param null  $field_group Optionally a form group to add form fields to
     *
     * @return array
     */
    public function getDisplayArray($field_data = [], $field_group = null, $use_default = false)
    {
        $custom_fields = [];
        foreach ($this->getFields() as $f_def) {
            $display                     = new FieldDisplayArray($this, $f_def, $field_data, $field_group, $use_default);
            $custom_fields[$f_def['id']] = $display;
        }

        return $custom_fields;
    }

    /**
     * Recreates an array we'd get from a posted form based on the values already on an object.
     * Useful when re-creating objects to pass through a validator.
     *
     * @param $object
     *
     * @return array
     */
    public function createFormArrayForObject($object)
    {
        $field_data = $this->getFieldDataForObject($object);

        $form_data = [];

        foreach ($field_data as $field_id => $data) {
            $f = $this->getFieldFromId($field_id);

            switch ($f->getTypeName()) {
                case 'choice':
                    if (!empty($data['children'])) {
                        $form_data['field_'.$field_id] = [];
                        foreach ($data['children'] as $k => $child) {
                            $form_data['field_'.$field_id][] = $k;
                        }
                    }
                    break;

                default:
                    if (!empty($data['value']) || (isset($data['value']) && ($data['value'] === 0 || $data['value'] === '0'))) {
                        $form_data['field_'.$field_id] = $data['value'];
                    }
                    break;
            }
        }

        return $form_data;
    }

    /**
     * Render field data to their 'text values.
     *
     * @param array $field_data
     *
     * @return array
     */
    public function getRenderedToText($field_data = [])
    {
        $custom_fields = [];
        foreach ($this->getFields() as $f_def) {
            $value = !empty($field_data[$f_def['id']]) && $field_data[$f_def['id']] !== 0 && $field_data[$f_def['id']] !== '0' ? $field_data[$f_def['id']] : null;

            $rendered = $value !== null ? $f_def->getHandler()->renderText($value) : null;

            $custom_fields[$f_def['id']] = [
                'rendered'      => trim($rendered),
                'elId'          => \Orb\Util\Util::requestUniqueIdString(),
                'hasValue'      => ($value !== null),
                'id'            => $f_def['id'],
                'name'          => 'field_'.$f_def['id'],
                'handler'       => $f_def->getHandler(),
                'field_def'     => $f_def,
                'title'         => $f_def['title'],
                'value'         => $value,
                'field_handler' => strtolower(\Orb\Util\Util::getBaseClassname($f_def->getHandler())),
            ];
        }

        return $custom_fields;
    }

    /**
     * return field title and value for CustomData.
     *
     * @param CustomDataAbstract $data
     *
     * @return array
     */
    public function renderTextForData(CustomDataAbstract $data)
    {
        $field = $data->root_field ?: $data->field;
        $value = !$data->root_field || $data->root_field === $data->field
            ? ['value' => $data->getData()]
            : ['value' => null, 'children' => [
                $data->field['id'] => ['value' => $data->getData(), 'children' => null],
            ]];

        $val = trim($field->getHandler()->renderText($value));

        return [
            'id'    => $field['id'],
            'title' => $field['title'],
            'value' => $val,
        ];
    }

    /**
     * Render field data form an object to their text values.
     *
     * @param $object
     *
     * @return array
     */
    public function getRenderedToTextForObject($object)
    {
        $field_data = $this->getFieldDataForObject($object);

        return $this->getRenderedToText($field_data);
    }

    /**
     * Create a field display array from an object.
     *
     * @param      $object
     * @param null $field_group
     *
     * @return array
     */
    public function getDisplayArrayForObject($object, $field_group = null)
    {
        $field_data = $this->getFieldDataForObject($object);

        // If the object has no id then it means it isn't persisted,
        // which means we should use the default value to show on a form somewhere
        $use_default = false;
        if ($object && !$object->getId()) {
            $use_default = true;
        }

        return $this->getDisplayArray($field_data, $field_group, $use_default);
    }

    /**
     * Create field display arrays for a collection of objects and their field object (raw values for their fields).
     *
     * $objects are the actual objects you want to process. For example $tickets of types Ticket
     * $field_objects are all those records custom field objects. For example, $ticket_custom_data of types CustomDataTicket
     *
     * You get back an array of display arrays, the same as youd get from getDisplayArrayForObject()
     *
     * @param array $objects
     * @param array $field_objects
     *
     * @return array
     */
    public function getDisplayArraysForObjectCollection(array $objects, array $field_objects)
    {
        $data = [];

        foreach ($objects as $object) {
            if (!isset($field_objects[$object->getId()])) {
                continue;
            }

            $field_data             = $this->createFieldDataFromArray($object, $field_objects[$object->getId()]);
            $data[$object->getId()] = $this->getDisplayArray($field_data, null);
        }

        return $data;
    }

    /**
     * Take custom field data from an obejct and return a field data array.
     *
     * @param $object
     *
     * @return array
     */
    public function getFieldDataForObject($object)
    {
        $prop = $this->options->get('custom_data_property');
        $data = $object ? $object->$prop : null;

        if (!$data) {
            $data = [];
        }

        return $this->createFieldDataFromArray($data);
    }

    /**
     * This converts a collection of data items into an array structure
     * that matches the hierarchy of field definitions.
     *
     * Custom field values in the database are 'flat', and when displaying values
     * we need to pass a proper structure to a field defition for rendering. This is
     * easy for simple fields like text or textarea, but we need this method for
     * complex fields that have multiple levels, like a choice.
     *
     * @param $fieldDatas
     *
     * @return array
     */
    public function createFieldDataFromArray($fieldDatas)
    {
        // Create a map of keys
        $dataKeys = [];
        foreach ($fieldDatas as $k => $v) {
            if ($v->getRootField()->isFileType()) {
                $dataKeys[$v->field->getId()][] = $k;
            } else {
                $dataKeys[$v->field->getId()] = $k;
            }
        }

        $data = $this->_createDataHierarchy($dataKeys, $fieldDatas, $this->getFields());

        return $data;
    }

    protected function _createDataHierarchy($data_keys, $field_datas, $field_defs)
    {
        $structure = [];

        foreach ($field_defs as $def) {
            $item = ['value' => null, 'children' => null];

            if (isset($data_keys[$def['id']])) {
                if ($def->isFileType()) {
                    foreach ($data_keys[$def['id']] as $valKey) {
                        $blobId = $field_datas[$valKey]->getValue();
                        if ($blobId) {
                            $blob = $this->em->getRepository(Blob::class)->find($blobId);
                            if ($blob) {
                                $item['value'][] = $blob;
                            }
                        }
                    }
                } else {
                    $item['value'] = $field_datas[$data_keys[$def['id']]]->getData();
                }

                $item['title'] = $def['title'];
            }

            if (isset($this->field_to_children[$def->getId()])) {
                $item['children'] = $this->_createDataHierarchy($data_keys, $field_datas, $this->field_to_children[$def->getId()]);
            }

            if ($item['value'] || $item['children'] || $item['value'] === 0 || $item['value'] === '0') {
                $structure[$def['id']] = $item;
            }
        }

        return $structure;
    }

    /**
     * Save a posted form of custom field data to an object.
     *
     * @param array $form
     * @param mixed $object
     * @param bool  $only_set
     * @param bool  $flushChanges
     */
    public function saveFormToObject(array $form, $object, $only_set = false, $flushChanges = true)
    {
        $this->setFormToObject($form, $object, $only_set);

        // BC: $em should be flushed outside this method
        if ($flushChanges) {
            $this->em->flush();
        }
    }

    /**
     * @param array             $form
     * @param CustomDefAbstract $fieldDef
     *
     * @return bool
     */
    private function fieldIsPresent(array $form, CustomDefAbstract $fieldDef)
    {
        if (array_key_exists('field_'.$fieldDef->getId(), $form)) {
            return true;
        }

        foreach (ObjectAlias\Converters::toMergedList($fieldDef->getAliases()) as $name) {
            if (array_key_exists($name, $form)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns a list of the field names which can resolve to more than one field.
     *
     * @param array               $form
     * @param CustomDefAbstract[] $fieldDefs
     *
     * @return array|int[]
     */
    private function findAmbiguousFieldReferences(array $form, $fieldDefs)
    {
        $refs = [];

        foreach ($fieldDefs as $def) {
            foreach (ObjectAlias\Converters::toMergedList($def->getAliases()) as $name) {
                $counter     = array_key_exists($name, $refs) ? $refs[$name] : 0;
                $refs[$name] = $counter + 1;
            }
        }

        return array_filter($refs, function ($value) {
            return $value > 1;
        });
    }

    public function setFormToObject(array $form, $object, $only_set = false)
    {
        $fields = $this->getFields();

        // When setting specific values, always operate on all enabled
        // fields because we might be in user interface but specifically want to set some field in code
        if ($only_set) {
            $fields = $this->getDefinedFields();
            $fields = array_filter($fields, function ($f) {
                return $f->is_enabled;
            });
        }

        $this->_orig_display = $this->getDisplayArrayForObject($object);

        $ambiguousRefs = $this->findAmbiguousFieldReferences($form, $fields);
        if (count($ambiguousRefs)) {
            throw new \DomainException('some field names can resolve to multiple custom fields');
        }

        /** @var CustomDefAbstract $field_def */
        foreach ($fields as $field_def) {
            if ($only_set && !$this->fieldIsPresent($form, $field_def)
            ) {
                continue;
            }

            /** @var HandlerAbstract $handler */
            $handler = $field_def->getHandler();

            if (!$data = $handler->getDataFromForm($form)) {
                $this->removeCustomDataOnObject($object, $field_def);
                continue;
            }

            // for multiple values only
            $fieldsIds = array_flip(array_map(function ($a) {
                return $a[0];
            }, $data));
            foreach ($field_def->getChildren() as $child) {
                if (!$customDataForField = $object->getCustomDataForField($child)) {
                    continue;
                }
                if (isset($fieldsIds[$child['id']])) {
                    continue;
                }

                // remove stored CustomData which are not present in current form
                $this->removeCustomDataOnObject($object, $child);
            }

            foreach ($data as $info) {
                $this->setCustomDataOnObject($object, $field_def, $info);
            }
        }

        $this->_orig_display = null;
    }

    /**
     * Returns an array of data objects of type $data_class based on form input.
     *
     * @return \Application\DeskPRO\Entity\CustomDataAbstract[]
     */
    public function getStrucutredDataFromForm(array $form, $data_class)
    {
        $structured_data = [];

        foreach ($this->getFields() as $field_def) {
            foreach ($field_def->getHandler()->getDataFromForm($form) as $in_data) {
                list($set_field_id, $value_type, $value) = $in_data;

                // The field we're actually saving under
                // Usually the same as $field_def, but not always
                // Ex: Choice fields we save under the actual choice option
                $set_field = null;

                if ($field_def->getId() == $set_field_id) {
                    $set_field = $field_def;
                } elseif (isset($this->field_to_children[$field_def->getId()])) {
                    foreach ($this->field_to_children[$field_def->getId()] as $c) {
                        if ($c->getId() == $set_field_id) {
                            $set_field = $c;
                            break;
                        }
                    }
                }

                // No value
                if ($value === null || $set_field === null) {
                    continue;
                }

                $data              = new $data_class();
                $data->field       = $set_field;
                $data[$value_type] = $value;

                $structured_data[] = $data;
            }
        }

        return $structured_data;
    }

    /**
     * @param                                               $object
     * @param \Application\DeskPRO\Entity\CustomDefAbstract $fieldDef
     * @param array                                         $in_data
     *
     * @return array
     */
    public function setCustomDataOnObject($object, CustomDefAbstract $fieldDef, array $in_data)
    {
        if (!$object) {
            return;
        }

        list($set_field_id, $value_type, $value) = $in_data;

        // The field we're actually saving under
        // Usually the same as $field_def, but not always
        // Ex: Choice fields we save under the actual choice option
        $set_field = null;

        if ($fieldDef->getId() == $set_field_id) {
            $set_field = $fieldDef;
        } elseif (isset($this->field_to_children[$fieldDef->getId()])) {
            foreach ($this->field_to_children[$fieldDef->getId()] as $c) {
                if ($c->getId() == $set_field_id) {
                    $set_field = $c;
                    break;
                }
            }
        }

        // No value
        if ($value === null || $set_field === null) {
            $this->removeCustomDataOnObject($object, $fieldDef);

            return;
        }

        if ($fieldDef->isFileType() || $fieldDef->isDataListType()) {
            if (!is_array($value)) {
                $value = [$value];
            }

            /** @var CustomDataAbstract[]|ArrayCollection $customData */
            $customData = $object->getCustomData();
            $existItems = [];
            foreach ($customData as $customDatum) {
                if ($customDatum->getRootField() !== $fieldDef) {
                    continue;
                }
                if (!in_array($customDatum->getData(), $value)) {
                    $customData->removeElement($customDatum);
                } else {
                    $existItems[] = $customDatum->getData();
                }
            }
            foreach ($value as $item) {
                if ($item && !in_array($item, $existItems)) {
                    $customDatum = $fieldDef->createCustomData();
                    $customDatum->setData($item);

                    $object->addCustomData($customDatum);
                }
            }

            return $customData;
        } else {
            $old_custom_data = $object->getCustomDataForField($set_field);

            $customDatum              = $old_custom_data ?: $this->createDataClass();
            $customDatum->field       = $set_field;
            $customDatum->root_field  = $fieldDef;
            $customDatum[$value_type] = $value;

            if (!$old_custom_data) {
                $object->addCustomData($customDatum);
            }

            // remove dupes
            foreach ($object->getCustomData() as $existCustomData) {
                if ($existCustomData->getField() === $set_field && $existCustomData !== $customDatum) {
                    $object->getCustomData()->removeElement($existCustomData);
                }
            }

            return $customDatum;
        }
    }

    /**
     * @param                                               $object
     * @param \Application\DeskPRO\Entity\CustomDefAbstract $field_def
     */
    public function removeCustomDataOnObject($object, CustomDefAbstract $field_def)
    {
        if (!$object) {
            return;
        }

        $prop = $this->options->get('custom_data_property');

        // Remove just def data (not all its parent data) because we update custom data now (to prevent unique key duplicate errors)
        // For multi values if $field_def is 'choice value' def then we remove just its data not all 'parent' choice values

        foreach ($object->$prop as $v) {
            if ($v->field->getId() == $field_def->getId() || ($v->field->parent && $v->field->parent->getId() == $field_def->getId())) {
                if ($object instanceof Ticket) {
                    $object->getStateChangeRecorder()->touchField('custom_data');
                }
                $object->custom_data->removeElement($v);
            }
        }
    }

    /**
     * Removes only a subset of the values of a field and flushes entity manager changes.
     *
     * Mainly exists because it's not clear when the entity manager is flushed.
     *
     * @todo investigate if can be removed
     *
     * @param                                               $object
     * @param \Application\DeskPRO\Entity\CustomDefAbstract $fieldDefinition
     * @param \Closure                                      $customDataFilter
     */
    public function removeSomeCustomDataOnObjectAndFlushChanges($object, CustomDefAbstract $fieldDefinition, \Closure $customDataFilter)
    {
        $this->removeSomeCustomDataOnObject($object, $fieldDefinition, $customDataFilter);
        // BC: $em should be flushed outside this method
        $this->em->flush();
    }

    /**
     * Removes only a subset of the values of a field. Mostly used for DataList fields.
     *
     * @param                                               $object
     * @param \Application\DeskPRO\Entity\CustomDefAbstract $fieldDefinition
     * @param \Closure                                      $customDataFilter
     */
    public function removeSomeCustomDataOnObject($object, CustomDefAbstract $fieldDefinition, \Closure $customDataFilter)
    {
        /** @var PersistentCollection $customData */
        $customData = $object->getCustomData();
        // filter only the custom data belonging to that field
        $fieldCustomData = array_filter(
            $customData->toArray(),
            function (CustomDataAbstract $customData) use ($fieldDefinition) {
                return $customData->getFieldId() === $fieldDefinition->getId();
            }
        );

        $unsetCustomDataList = array_filter(
            $fieldCustomData,
            function (CustomDataAbstract $customData) use ($customDataFilter) {
                return $customDataFilter($customData);
            }
        );

        foreach ($unsetCustomDataList as $unsetCustomData) {
            $customData->removeElement($unsetCustomData);
        }
    }

    /**
     * @return \Application\DeskPRO\Entity\CustomDataAbstract
     */
    public function createDataClass()
    {
        $classname = $this->options->get('data_entity_class');

        return new $classname();
    }

    /**
     * Adds custom field API data to an object along with rendered values.
     *
     * @param mixed $object
     * @param array $data
     */
    public function addApiData($object, array &$data)
    {
        if (!empty($data['custom_data'])) {
            foreach ($data['custom_data'] as &$_f) {
                if (!empty($_f['root_field']) && $_f['root_field']['id'] == $_f['id']) {
                    unset($_f['root_field']);
                }
            }
            unset($_f);

            $values = $this->getRenderedToTextForObject($object);
            foreach ($values as $fid => $v) {
                $data["field{$fid}"] = $v['rendered'];

                foreach ($data['custom_data'] as &$_f) {
                    if ($_f['id'] == $fid) {
                        $_f['rendered_value'] = $v['rendered'];
                    }
                }
                unset($_f);
            }
        }
    }
}
