<?php

namespace Application\DeskPRO\CustomFields;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity\Person;
use \Application\DeskPRO\Entity\TicketQueue;
use \Symfony\Component\DependencyInjection\ContainerAware;

class Util
{
	/**
	 * This converts a collection of data items into an array structure
	 * that matches the hierarchy of field definitions.
	 * 
	 * Custom field values in the database are 'flat', and when displaying values
	 * we need to pass a proper structure to a field defition for rendering. This is
	 * easy for simple fields like text or textarea, but we need this method for 
	 * complex fields that have multiple levels, like a date.
	 *
	 * @see Application\DeskPRO\CustomFields\Handler\HandlerAbstract\renderContext
	 * 
	 * @param $field_datas
	 * @param $field_defs
	 * @return array
	 */
	public function createDataHierarchy($field_datas, $field_defs)
	{
		// Create a map of keys
		$data_keys = array();
		foreach ($field_datas as $k => $v) {
			$data_keys[$v['field']['id']] = $k;
		}

		return $this->_createDataHierarchy($data_keys, $field_datas, $field_defs);
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
}