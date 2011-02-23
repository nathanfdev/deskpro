<?php

namespace Application\DeskPRO\CustomFields;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity\Person;
use \Application\DeskPRO\Entity\TicketQueue;
use \Symfony\Component\DependencyInjection\ContainerAware;

abstract class AbstractFields
{
	const ENTITY_CLASS = 'Application\\DeskPRO\\Entity\\CustomDefAbstract';
	const ENTITY_NAME  = 'DeskPRO:CustomDefAbstract';

	/**
	 * Get a collection of all defined person fields
	 *
	 * @return array
	 */
	public function getFields()
	{
		$fields = App::getOrm()->getRepository(static::ENTITY_NAME)->getFields();

		return $fields;
	}

	public function getEnabledFields()
	{
		//TODO

		return $this->getFields();
	}

	public function getFieldFromId($field_id)
	{
		$field = App::getOrm()->getRepository(static::ENTITY_NAME)->find($field_id);

		return $field;
	}

	public function getFieldsDisplayArray($field_defs, $data_structured = array(), $field_group = null)
	{
		$custom_fields = array();
		$has_value = false;
		foreach ($field_defs as $f_def) {
			$value = !empty($data_structured[$f_def['id']]) ? $data_structured[$f_def['id']] : null;

			$f = $f_def->getHandler()->getFormField($value);

			if ($field_group) {
				$field_group->add($f);
			}

			$rendered = $value ? $f_def->getHandler()->renderHtml($value) : null;
			if ($rendered) $has_value = true;

			$custom_fields[$f_def['id']] = array(
				'id' => $f_def['id'],
				'field_def' => $f_def,
				'title' => $f_def['title'],
				'form' => $f,
				'rendered' =>  $rendered
			);
		}

		return $custom_fields;
	}


	public function getEntityName()
	{
		return static::ENTITY_NAME;
	}

	public function getEntityClassname()
	{
		return static::ENTITY_CLASS;
	}
}