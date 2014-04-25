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
 * @package Importer
 */

namespace Application\ImportBundle\RecordMapper;

class RecordMapperRegistry implements \ArrayAccess, \IteratorAggregate
{
	/**
	 * @var \Application\ImportBundle\RecordMapper\RecordMapperInterface[]
	 */
	private $mappers = array();


	/**
	 * @param string $type
	 * @param RecordMapperInterface $mapper
	 */
	public function addMapper($type, RecordMapperInterface $mapper)
	{
		if (isset($this->mappers[$type])) {
			throw new \InvalidArgumentException("Mapper already exists: $type");
		}

		$this->mappers[$type] = $mapper;
	}


	/**
	 * @param string $type
	 * @return RecordMapperInterface
	 */
	public function getMapper($type)
	{
		if (!isset($this->mappers[$type])) {
			throw new \InvalidArgumentException("Unknown mapper type: $type");
		}

		return $this->mappers[$type];
	}


	/**
	 * @param string $type
	 * @param mixed $value
	 * @return int|null
	 */
	public function findIdFromMappedValue($type, $value)
	{
		return $this->getMapper($type)->findIdFromValue($value);
	}


	/**
	 * @param string $type
	 * @param mixed $record
	 */
	public function learnMapping($type, $record)
	{
		$mapper = $this->getMapper($type);
		if (!($mapper instanceof LearnableRecordMapperInterface)) {
			throw new \InvalidArgumentException("$type is not a learnable record mapper");
		}

		$mapper->learnRecord($record);
	}


	/**
	 * {@inheritDoc}
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->mappers);
	}


	/**
	 * {@inheritDoc}
	 */
	public function offsetExists($offset)
	{
		return isset($this->mappers[$offset]);
	}


	/**
	 * {@inheritDoc}
	 */
	public function offsetGet($offset)
	{
		return $this->mappers[$offset];
	}


	/**
	 * {@inheritDoc}
	 */
	public function offsetSet($offset, $value)
	{
		$this->addMapper($offset, $value);
	}


	/**
	 * {@inheritDoc}
	 */
	public function offsetUnset($offset)
	{
		unset($this->mappers[$offset]);
	}
}