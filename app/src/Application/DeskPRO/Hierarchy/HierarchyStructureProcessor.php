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
 */

namespace Application\DeskPRO\Hierarchy;

use Doctrine\ORM\EntityManager;

/**
 * This helps take an array of arrays that describe a tree, and
 * persist it to the database.
 */
class HierarchyStructureProcessor
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var string
	 */
	private $entity_name;

	/**
	 * @var array
	 */
	private $properties;


	/**
	 * @param EntityManager $em
	 * @param string        $entity_name   The name of the entity to save to
	 * @param string[]      $properties    Array of property names to assign from the array (id, display_order and title are always used/detected)
	 */
	public function __construct(EntityManager $em, $entity_name, array $properties = array())
	{
		$this->em          = $em;
		$this->entity_name = $entity_name;
		$this->properties  = $properties;
	}


	/**
	 * Returns an array of entities that are ready to be saved or validated
	 *
	 * @param array $structure
	 * @return array
	 */
	public function getRecords(array $structure)
	{
		$recs                = array();
		$child_to_parent_map = array();

		#------------------------------
		# First pass is to gather info:
		# - Loads cats
		# - Creates pristine entities for new ones
		# - Maps children to their parents
		#------------------------------

		$cat_ids = array();
		$classname = $this->em->getRepository($this->entity_name)->getClassName();
		foreach ($structure as $cat) {
			if (empty($cat['@is_new'])) {
				$cat_ids[] = $cat['id'];
			} else {
				$obj = new $classname();
				$recs[$cat['id']] = $obj;
			}

			if (!empty($cat['parent_id']) && $cat['parent_id']) {
				$child_to_parent_map[$cat['id']] = $cat['parent_id'];
			}
		}

		if ($cat_ids) {
			foreach ($this->em->getRepository($this->entity_name)->getByIds($cat_ids) as $k => $v) {
				$recs[$k] = $v;
			}
		}

		#------------------------------
		# Second pass applies the array to the object
		#------------------------------

		foreach ($structure as $cat) {
			$cat_id = $cat['id'];
			if (!isset($recs[$cat_id])) {
				throw new \Exception("Could not find category in collection: " . $cat_id);
			}

			$obj = $recs[$cat_id];
			$this->applyProperties($obj, $cat);

			// Also hook up children to their parents
			if (isset($child_to_parent_map[$cat_id])) {
				$parent_obj = $recs[$child_to_parent_map[$cat_id]];
				$obj->parent = $parent_obj;
			} else {
				$obj->parent = null;
			}
		}

		return $recs;
	}



	/**
	 * @param array $records
	 * @param bool $remove_missing  True to delete records that are not in the record array (e.g., they were deleted from the structure)
	 * @return array
	 */
	public function saveRecords(array $records, $remove_missing = false)
	{
		if ($remove_missing) {
			$have_ids = array();
			foreach ($records as $r) {
				if ($r->id) {
					$have_ids[] = $r->id;
				}
			}

			$missing_recs = $this->em->createQuery("
				SELECT r
				FROM {$this->entity_name} r
				WHERE r NOT IN (?0)
			")->execute(array($have_ids));

			foreach ($missing_recs as $r) {
				$this->em->remove($r);
			}
		}

		foreach ($records as $r) {
			$this->em->persist($r);
		}

		$this->em->getConnection()->beginTransaction();
		try {
			$this->em->flush();
			$this->em->commit();
		} catch (\Exception $e) {
			$this->em->rollback();
			throw $e;
		}

		return $records;
	}


	/**
	 * This is the same as calling getRecords followed by saveRecords
	 * @param array $structure
	 * @return array
	 */
	public function save(array $structure)
	{
		$recs = $this->getRecords($structure);
		$this->saveRecords($recs, true);

		return $recs;
	}


	/**
	 * Apply properties to the category object
	 *
	 * @param mixed $object
	 * @param array $values
	 */
	private function applyProperties($object, array $values)
	{
		foreach ($this->properties as $k) {
			if (array_key_exists($k, $values)) {
				$object->$k = $values[$k];
			}
		}

		// Built-in props
		foreach (array('title', 'display_order') as $k) {
			if (array_key_exists($k, $values)) {
				$object->$k = $values[$k];
			}
		}
	}
}