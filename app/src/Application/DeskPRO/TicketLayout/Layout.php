<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\TicketLayout;

use Orb\Types\JsonObjectSerializable;
use Orb\Util\Arrays;
use Orb\Util\Strings;

class Layout implements \IteratorAggregate, \Serializable, JsonObjectSerializable
{
	/**
	 * @var LayoutField[]
	 */
	private $fields = array();


	/**
	 * @param array $fields
	 */
	public function setAll(array $fields)
	{
		$this->fields = array();
		foreach ($fields as $f) {
			$this->add($f);
		}
	}


	/**
	 * @param LayoutField $field
	 * @param string $before_field Field ID of a field to insert the field before. If not specified, the field is added to the end.
	 */
	public function add(LayoutField $field, $before_field = null)
	{
		$id = $field->getId();
		if (isset($this->fields[$id])) {
			unset($this->fields[$id]);
		}

		$did_add = false;
		if ($before_field) {
			$pos = Arrays::findKey($this->fields, function($v) use ($before_field) {
				return $v->getId() == $before_field;
			});
			if ($pos !== null) {
				$all_fields = $this->fields;
				$this->fields = array();
				foreach ($all_fields as $k => $v) {
					if ($k == $before_field) {
						$did_add = true;
						$this->fields[$id] = $field;
					}

					$this->fields[$k] = $v;
				}
			}
		}

		if (!$did_add) {
			$this->fields[$id] = $field;
		}
	}


	/**
	 * @param LayoutField $field
	 */
	public function prepend(LayoutField $field)
	{
		$id = $field->getId();
		if (isset($this->fields[$id])) {
			unset($this->fields[$id]);
		}

		Arrays::unshiftAssoc($this->fields, $id, $field);
	}


	/**
	 * @param string $id
	 * @return bool
	 */
	public function has($id)
	{
		return isset($this->fields[$id]);
	}


	/**
	 * @param string $id
	 * @return LayoutField
	 */
	public function get($id)
	{
		if (!isset($this->fields[$id])) {
			return new \InvalidArgumentException("Invalid field ID: $id");
		}

		return $this->fields[$id];
	}


	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->fields);
	}


	/**
	 * @return LayoutField[]
	 */
	public function all()
	{
		return $this->fields;
	}


	/**
	 * @param string $id
	 */
	public function remove($id)
	{
		unset($this->fields[$id]);
	}


	/**
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->fields);
	}


	/**
	 * @return string
	 */
	public function compileJsObj()
	{
		$js = "(function() {\n";
		$js .= "\tvar fields = [\n";

		$fields_js = array();
		foreach ($this->fields as $field) {
			if ($field->hasCriteria()) {
				$check_fn = trim(Strings::modifyLines($field->compileJsCheck(), "\t\t\t\t"));
			} else {
				$check_fn = 'null';
			}

			$bit_js = "\t\t{\n";
			$bit_js .= "\t\t\tid:                    '{$field->getId()}',\n";
			$bit_js .= "\t\t\tfield_type:            '{$field->getFieldType()}',\n";
			$bit_js .= "\t\t\tfield_id:              " . ($field->getFieldId() ? "'{$field->getFieldId()}'" : 'null') . ",\n";
			$bit_js .= "\t\t\tisVisibleOnNew:        " . ($field->isVisibleOnNew() ? 'true' : 'false') . ",\n";
			$bit_js .= "\t\t\tisVisibleOnView:       " . ($field->isVisibleOnView() ? 'true' : 'false') . ",\n";
			$bit_js .= "\t\t\tisVisibleOnViewAlways: " . ($field->isVisibleOnViewAlways() ? 'true' : 'false') . ",\n";
			$bit_js .= "\t\t\tisVisibleOnEdit:       " . ($field->isVisibleOnEdit() ? 'true' : 'false') . ",\n";
			$bit_js .= "\t\t\tcheckFn:               $check_fn\n";
			$bit_js .= "\t\t}";
			$fields_js[] = $bit_js;
		}

		$js .= implode(",\n", $fields_js) . "\n\t];\n\n";

		$js .= "\treturn {\n";
		$js .= "\t\tgetMatchingFields: function(ticket) {\n";
		$js .= "\t\t\tvar match = [];\n";
		$js .= "\t\t\tfor(var i = 0; i < fields.length; i++) { if (fields[i].checkFn(ticket)) match.push(fields[i]); }\n";
		$js .= "\t\t\treturn match;\n";
		$js .= "\t\t},\n";
		$js .= "\t\tgetFields: function() {\n";
		$js .= "\t\t\treturn fields;\n";
		$js .= "\t\t}\n";
		$js .= "\t};\n";

		$js .= "})()";

		return $js;
	}


	/**
	 * @return array
	 */
	public function exportToArray()
	{
		$data = array();

		$data['version']  = 1;
		$data['fields'] = array();
		foreach ($this->fields as $f) {
			$data['fields'][] = $f->exportToArray();
		}

		return $data;
	}


	/**
	 * @return string
	 */
	public function exportToJson()
	{
		return json_encode($this->exportToArray());
	}


	/**
	 * @param array $data
	 */
	public function importFromArray(array $data)
	{
		foreach ($data['fields'] as $f) {
			$field = new LayoutField($f['field_type'], $f['field_id']);
			$field->setOptionsFromArray($f['options']);
			$this->fields[$field->getId()] = $field;
		}
	}


	/**
	 * @return string
	 */
	public function serialize()
	{
		return $this->exportToJson();
	}


	/**
	 * @param string $data
	 */
	public function unserialize($data)
	{
		$data = json_decode($data, true);
		$this->importFromArray($data);
	}


	/**
	 * @return array
	 */
	public function serializeJsonArray()
	{
		return $this->exportToArray();
	}


	/**
	 * @param array $data
	 * @return Layout
	 */
	public static function unserializeJsonArray(array $data)
	{
		$obj = new self();
		$obj->importFromArray($data);
		return $obj;
	}

	/**
	 * @param $type
	 * @return array
	 */
	public function getIdsOfFieldType($type)
	{
		$ret = array();
		foreach ($this->fields as $field) {
			if ($type === $field->getFieldType()) {
				$ret[] = $field->getFieldId();
			}
		}
		return $ret;
	}
}