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

namespace Application\DeskPRO\ORM\StateChange;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\PersistentCollection;
use Orb\Util\Arrays;

class ChangeCollection implements ChangeInterface
{
	/**
	 * @var string
	 */
	private $field_id;

	/**
	 * @var array
	 */
	private $old;

	/**
	 * @var array
	 */
	private $new;

	/**
	 * @var bool
	 */
	private $is_same = false;

	/**
	 * @var array
	 */
	private $add_elements = array();

	/**
	 * @var array
	 */
	private $del_elements = array();


	/**
	 * @param string     $field_id
	 * @param Collection $coll
	 * @return ChangeCollection
	 */
	public static function newFromPersistedCollection($field_id, Collection $coll, $old = array())
	{
		if ($coll instanceof PersistentCollection) {
			$new = $coll->toArray();
		} else {
			$new = $coll->toArray();
		}

		return new self($field_id, $old, $new);
	}


	/**
	 * @param string $field_id
	 * @param mixed  $old
	 * @param mixed  $new
	 */
	public function __construct($field_id, array $old = null, array $new = null)
	{
		$this->field_id = $field_id;
		$this->old      = $old;
		$this->new      = $new;

		// Check for null
		if ($old === $new) {
			$this->is_same = true;
		} else {
			if ($this->old && $this->new) {
				$this->del_elements = Arrays::arrayDiffAssocIdentity($this->old, $this->new);
				$this->add_elements = Arrays::arrayDiffAssocIdentity($this->new, $this->old);
			} else if ($this->old && !$this->new) {
				$this->del_elements = $this->old;
			} else if ($this->new && !$this->old) {
				$this->add_elements = $this->new;
			}

			if (!$this->del_elements && !$this->add_elements) {
				$this->is_same = true;
			}
		}
	}


	/**
	 * @return string
	 */
	public function getField()
	{
		return $this->field_id;
	}


	/**
	 * @return array
	 */
	public function getOld()
	{
		return $this->old;
	}


	/**
	 * @return array
	 */
	public function getNew()
	{
		return $this->new;
	}


	/**
	 * @return bool
	 */
	public function isSame()
	{
		return $this->is_same;
	}


	/**
	 * @return bool
	 */
	public function isCollection()
	{
		return true;
	}


	/**
	 * @return bool
	 */
	public function isEntity()
	{
		return false;
	}


	/**
	 * @return array
	 */
	public function getAddedElements()
	{
		return $this->add_elements;
	}


	/**
	 * @return array
	 */
	public function getRemovedElements()
	{
		return $this->del_elements;
	}

	public function setAddedElements(array $added)
	{
		$this->add_elements = $added;
	}

	public function setRemovedElements(array $removed)
	{
		$this->del_elements = $removed;
	}
}