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

namespace Application\DeskPRO\ORM\StateChange;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\Common\Collections\Collection;

class StateChangeRecorder
{
	/**
	 * @var \Application\DeskPRO\ORM\StateChange\ChangeInterface[]
	 */
	private $changes = array();

	/**
	 * @var array[]
	 */
	private $changes_by_field = array();


	/**
	 * Record a new change
	 *
	 * @param string $field_id
	 * @param mixed  $old
	 * @param mixed  $new
	 * @param bool   $skip_same
	 * @return ChangeArray|ChangeDate|ChangeObject|ChangeSimple|null
	 */
	public function record($field_id, $old, $new, $skip_same = true)
	{
		if ($old === null && $new === null) {
			$use_type = $old;
		} elseif ($old === null) {
			$use_type = $new;
		} else {
			$use_type = $old;
		}

		if (is_object($use_type)) {
			if ($use_type instanceof \DateTime) {
				$change = new ChangeDate($use_type);
			} else if ($use_type instanceof DomainObject) {
				$change = new ChangeObject($field_id, $old, $new);
			} else {
				$change = new ChangeObject($field_id, $old, $new);
			}
		} else if (is_array($use_type)) {
			$change = new ChangeArray($field_id, $old, $new);
		} else {
			$change = new ChangeSimple($field_id, $old, $new);
		}

		if ($skip_same && $change->isSame()) {
			return null;
		}

		$this->changes[] = $change;

		if (!isset($this->changes_by_field[$field_id])) {
			$this->changes_by_field[$field_id] = array();
		}
		$this->changes_by_field[$field_id][] = $change;

		return $change;
	}


	/**
	 * @param string     $field_id
	 * @param Collection $coll
	 * @param bool       $skip_same
	 * @return ChangeCollection|null
	 */
	public function recordCollection($field_id, Collection $coll, $skip_same = true)
	{
		$change = ChangeCollection::newFromPersistedCollection($field_id, $coll);
		if ($skip_same && $change->isSame()) {
			return null;
		}

		$this->changes[] = $change;

		if (!isset($this->changes_by_field[$field_id])) {
			$this->changes_by_field[$field_id] = array();
		}
		$this->changes_by_field[$field_id] = $change;

		return $change;
	}


	/**
	 * @param string $field_id
	 * @return bool
	 */
	public function hasChangedField($field_id)
	{
		return isset($this->changes_by_field[$field_id]);
	}


	/**
	 * @return ChangeInterface[]
	 */
	public function getChanges()
	{
		return $this->changes;
	}


	/**
	 * @param string $field_id
	 * @return array
	 */
	public function getChangesForField($field_id)
	{
		return isset($this->changes_by_field[$field_id]) ? $this->changes_by_field[$field_id] : array();
	}


	/**
	 * @return string[]
	 */
	public function getChangedFields()
	{
		return array_keys($this->changes_by_field);
	}


	/**
	 * @param $field_id
	 * @return ChangeInterface
	 */
	public function getFirstChangeForField($field_id)
	{
		if (!isset($this->changes_by_field[$field_id])) {
			return null;
		}

		return $this->changes_by_field[$field_id][0];
	}


	/**
	 * @param $field_id
	 * @return null
	 */
	public function getLastChangeForField($field_id)
	{
		if (!isset($this->changes_by_field[$field_id])) {
			return null;
		}

		$idx = count($this->changes_by_field[$field_id]) - 1;
		return $this->changes_by_field[$field_id][$idx];
	}


	/**
	 * @param string $field_id
	 * @return mixed
	 * @throws \InvalidArgumentException
	 */
	public function getOriginalValueForField($field_id)
	{
		$change = $this->getFirstChangeForField($field_id);
		if (!$change) {
			throw new \InvalidArgumentException("Field was not modified, cannot get the original version using the change manager");
		}

		return $change->getOld();
	}


	/**
	 * @param  string $field_id
	 * @return mixed
	 * @throws \InvalidArgumentException
	 */
	public function getPreviousValueForField($field_id)
	{
		$change = $this->getLastChangeForField($field_id);
		if (!$change) {
			throw new \InvalidArgumentException("Field was not modified, cannot get the original version using the change manager");
		}

		return $change->getOld();
	}
}