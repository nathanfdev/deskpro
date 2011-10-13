<?php

namespace Application\DeskPRO\CustomFields;

use Application\DeskPRO\App;
use Orb\Util\Util;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Doctrine\ORM\EntityManager;

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
	 * @var \Doctrine\ORM\Doctrine\DBAL\Connection
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
	 * Array of fields
	 * @var array
	 */
	protected $fields = null;

	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 */
	public function __construct(EntityManager $em, array $options)
	{
		$this->em = $em;
		$this->db = $em->getConnection();

		$this->options = new \Orb\Util\CheckedOptionsArray($options);
		$this->options->ensureRequired(array(
			'entity_class',
			'entity_name',
			'data_entity_name',
			'data_entity_class',
		));

		$this->options->setArrayDefault(array(
			'custom_data_property' => 'custom_data'
		));
	}

	/**
	 * Get a collection of all top-level (parent) fields
	 *
	 * @return array
	 */
	public function getFields()
	{
		if ($this->fields === null) {
			$this->fields = array();
			$all_fields = $this->em->getRepository($this->options->get('entity_name'))->getEnabledFields();
			foreach ($all_fields as $f) {
				if (!$f->parent) {
					$this->fields[$f->id] = $f;
				}
			}
		}

		return $this->fields;
	}


	/**
	 * Get a field from an ID
	 *
	 * @param $field_id
	 * @return \Application\DeskPRO\Entity\CustomDefAbstract
	 */
	public function getFieldFromId($field_id)
	{
		$this->getFields();
		return isset($this->fields[$field_id]) ? $this->fields[$field_id] : null;
	}


	/**
	 * Get a display array for rendering a field
	 *
	 * @param array $field_data  An array of structured data from the database
	 * @param null $field_group       Optionally a form group to add form fields to
	 * @return array
	 */
	public function getDisplayArray($field_data = array(), $field_group = null)
	{
		$custom_fields = array();
		$has_value = false;
		foreach ($this->getFields() as $f_def) {
			$value = !empty($field_data[$f_def['id']]) ? $field_data[$f_def['id']] : null;

			$f = $f_def->getHandler()->getFormField($value);

			$name = 'field_' . $f_def['id'];

			if ($field_group) {
				$field_group->add($f);
				$form = $field_group->getForm();
				$formView = $form->createView();
				$formView = $formView[$name];
			} else {
				$form = $f->getForm();
				$formView = $form->createView();
			}

			$rendered = $value ? $f_def->getHandler()->renderHtml($value) : null;
			if ($rendered) $has_value = true;

			$custom_fields[$f_def['id']] = array(
				'elId'            => Util::requestUniqueIdString(),
				'id'              => $f_def['id'],
				'name'            => 'field_' . $f_def['id'],
				'handler'         => $f_def->getHandler(),
				'field_def'       => $f_def,
				'title'           => $f_def['title'],
				'form'            => $form,
				'formView'        => $formView,
				'value'           => $value,
				'field_handler'   => strtolower(Util::getBaseClassname($f_def->getHandler())),
			);
		}

		return $custom_fields;
	}


	/**
	 * Create a field display array from an object
	 *
	 * @param $object
	 * @param null $field_group
	 * @return void
	 */
	public function getDisplayArrayForObject($object, $field_group = null)
	{
		$field_data = $this->getFieldDataForObject($object);
		return $this->getDisplayArray($field_data, $field_group);
	}


	/**
	 * Take custom field data from an obejct and return a field data array.
	 *
	 * @param $object
	 * @return array
	 */
	public function getFieldDataForObject($object)
	{
		$prop = $this->options->get('custom_data_property');
		$data = $object->$prop;

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
	 * @param $field_datas
	 * @return array
	 */
	public function createFieldDataFromArray($field_datas)
	{
		// Create a map of keys
		$data_keys = array();
		foreach ($field_datas as $k => $v) {
			$data_keys[$v['field']['id']] = $k;
		}

		return $this->_createDataHierarchy($data_keys, $field_datas, $this->getFields());
	}

	protected function _createDataHierarchy($data_keys, $field_datas, $field_defs)
	{
		$structure = array();

		foreach ($field_defs as $def) {
			$structure[$def['id']] = array('value' => null, 'children' => null);
			if (isset($data_keys[$def['id']])) {
				$structure[$def['id']]['value'] = $field_datas[$data_keys[$def['id']]]->getData();
			}

			if ($def['children']) {
				$structure[$def['id']]['children'] = $this->_createDataHierarchy($data_keys, $field_datas, $def['children']);
			}
		}

		return $structure;
	}


	/**
	 * Save a posted form of custom field data to an object
	 *
	 * @param array $form_data
	 * @param $object
	 * @return void
	 */
	public function saveFormToObject(array $form, $object)
	{
		foreach ($this->getFields() as $field_def) {
			foreach ($field_def->getHandler()->getDataFromForm($form_data) as $info) {
				$this->setCustomDataOnObject($object, $field_def, $info);
				$ticket->setCustomData($info[0], $info[1], $info[2]);
			}
		}
	}


	/**
	 * @param $object
	 * @param \Application\DeskPRO\Entity\CustomDefAbstract $field_def
	 * @param array $in_data
	 * @return array|null
	 */
	public function setCustomDataOnObject($object, CustomDefAbstract $field_def, array $in_data)
	{
		$field_id = $field_def->id;
		$prop = $this->options->get('custom_data_property');

		list(, $value_type, $value) = $in_data;

		// Remove whatever we have before
		// We'll just re-insert if its still there
		$this->removeCustomDataOnObject($object, $field_def);

		// No value
		if ($value === null) {
			return null;
		}

		$custom_data = $this->createDataClass();
		$custom_data['field'] = $field_def;
		$custom_data[$value_type] = $value;

		$object->addCustomData($custom_data);

		return $custom_data;
	}


	/**
	 * @param $object
	 * @param \Application\DeskPRO\Entity\CustomDefAbstract $field_def
	 * @return void
	 */
	public function removeCustomDataOnObject($object, CustomDefAbstract $field_def)
	{
		$prop = $this->options->get('custom_data_property');
		if ($field_def->parent) {
			if (isset($object->$prop[$field_def->parent->id])) {
				$object->$prop->remove($field_def->parent->id);
			}
		}

		if (isset($object->$prop[$field_id])) {
			$this->$prop->remove($field_id);
		}
	}


	/**
	 * @return \Application\DeskPRO\Entity\CustomDataAbstract
	 */
	public function createDataClass()
	{
		$classname = $this->options->get('data_entity_class');
		return new $classname;
	}
}
