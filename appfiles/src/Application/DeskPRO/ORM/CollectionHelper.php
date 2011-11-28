<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category ORM
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\ORM;

class CollectionHelper
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;
	protected $entity;
	protected $prop;

	public function __construct(\Doctrine\ORM\EntityManager $em, $entity, $prop)
	{
		$this->em = $em;
		$this->entity = $entity;
		$this->prop = $prop;
	}

	/**
	 * Given an array of records we want the entity to contain ("only $set"),
	 * get an array of records that need to be added or removed. Essentially an easy diff
	 *
	 * @param array $set
	 */
	public function getAddRemoveForSet(array $set)
	{
		$prop = $this->prop;

		$have_ids = array();
		$want_ids = array();

		foreach ($this->entity->$prop as $item) {
			$have_ids[] = $item->id;
		}

		foreach ($set as $item) {
			$want_ids[] = $item->id;
		}

		$add_ids = array_diff($want_ids, $have_ids);
		$del_ids = array_diff($have_ids, $want_ids);

		return array(
			'add' => $add_ids,
			'del' => $del_ids,
		);
	}


	/**
	 * Add or remove from the collection so it matcehs $set.
	 *
	 * @param array $set
	 */
	public function setCollection(array $set)
	{
		$prop = $this->prop;

		$info = $this->getAddRemoveForSet($set);
		$add_ids = $info['add'];
		$del_ids = $info['del'];

		foreach ($del_ids as $id) {
			foreach ($this->entity->$prop as $k => $item) {
				if ($item->id == $id) {
					$this->entity->$prop->removeKey($k);
					break;
				}
			}
		}

		foreach ($set as $item) {
			if (in_array($item->id, $add_ids)) {
				$this->entity->$prop->add($item);
			}
		}
	}
}
