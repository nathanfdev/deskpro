<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

class Layout implements \IteratorAggregate, \Serializable
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
	 */
	public function add(LayoutField $field)
	{
		$id = $field->getId();
		if (isset($this->fields[$id])) {
			unset($this->fields[$id]);
		}

		$this->fields[$id] = $field;
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
	 * @return \ArrayIterator|\Traversable
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->fields);
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
			$this->fields[] = $field;
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
}