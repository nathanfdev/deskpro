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

namespace Application\DeskPRO\Translate;

use Application\DeskPRO\Entity\ObjectLang;
use Application\DeskPRO\ORM\EntityManager;

class ObjectLangRepository
{
	/**
	 * @var \Application\DeskPRO\ORM\EntityManager
	 */
	protected $em;

	/**
	 * Loaded lang objects
	 *
	 * @var array
	 */
	protected $loaded = array();

	/**
	 * @var array
	 */
	protected $queued_objects;


	/**
	 * @param EntityManager $em
	 */
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}


	/**
	 * @param int|\Application\DeskPRO\Entity\Language $lang
	 * @param object|string $object
	 * @return bool
	 */
	public function isLoaded($lang, $object)
	{
		$lang_id = is_object($lang) ? $lang->getId() : $lang;
		$obj_ref = is_object($object) ? $object->getObjectRef() : $object;

		return isset($this->loaded[$obj_ref][$lang_id]);
	}


	/**
	 * Get all objects loaded on a rec
	 *
	 * @param object|string $object
	 * @return \Application\DeskPRO\Entity\ObjectLang[]
	 */
	public function getLoadedRecs($object)
	{
		$obj_ref = is_object($object) ? $object->getObjectRef() : $object;

		if (!isset($this->loaded[$obj_ref])) {
			return array();
		}

		$recs = array();
		foreach ($this->loaded[$obj_ref] as $lang => $lang_recs) {
			$recs = array_merge($recs, $lang_recs);
		}

		return $recs;
	}


	/**
	 * Get the ObjectLang record for a given property. Returns null if no such record exists.
	 *
	 * @param int|\Application\DeskPRO\Entity\Language $lang
	 * @param object|string $object
	 * @param string $prop_name
	 * @return \Application\DeskPRO\Entity\ObjectLang
	 */
	public function getRec($lang, $object, $prop_name)
	{
		$prop_name = strtolower($prop_name);
		$lang_id = is_object($lang) ? $lang->getId() : $lang;
		$obj_ref = is_object($object) ? $object->getObjectRef() : $object;

		if (!$this->isLoaded($lang_id, $obj_ref)) {
			$this->preloadObject($lang_id, $obj_ref);
			$this->runPreload();
		}

		if (!isset($this->loaded[$obj_ref][$lang_id][$prop_name])) {
			return null;
		}

		return $this->loaded[$obj_ref][$lang_id][$prop_name];
	}


	/**
	 * Sets the value on a phrase lang. A new record will be created automatically if one doesnt exist.
	 *
	 * @param int|\Application\DeskPRO\Entity\Language $lang
	 * @param object|string $object
	 * @param string $prop_name
	 * @param string $text
	 * @return ObjectLang
	 */
	public function setRec($lang, $object, $prop_name, $text)
	{
		$rec = $this->getRec($lang, $object, $prop_name);
		if (!$rec) {
			$rec = ObjectLang::createObjectLang($lang, $object, $prop_name, $text);
			$this->registerRec($rec);
		}

		return $rec;
	}


	/**
	 * Get the value of a given property. This is the actual translated text.
	 *
	 * @param int|\Application\DeskPRO\Entity\Language $lang
	 * @param object|string $object
	 * @param string $prop_name
	 * @return string
	 */
	public function get($lang, $object, $prop_name)
	{
		$rec = $this->getRec($lang, $object, $prop_name);
		if (!$rec) {
			return null;
		}

		return $rec->getValue();
	}


	/**
	 * Registeres an object lang record onto this object. E.g., it might be one that we are about
	 * to persist, or one we want to keep unpersisted.
	 *
	 * @param \Application\DeskPRO\Entity\ObjectLang $rec
	 */
	public function registerRec($rec)
	{
		$lang_id = $rec->language->getId();
		$obj_ref = $rec->ref;

		// If we add this one new record, the repository
		// will think we have preloaded it. So we should make
		// sure we have all of the objects loaded already
		if (!$this->isLoaded($lang_id, $obj_ref)) {
			$this->preloadObject($lang_id, $obj_ref);
			$this->runPreload();
		}

		if (!isset($this->loaded[$obj_ref])) {
			$this->loaded[$obj_ref] = array();
		}
		if (!isset($this->loaded[$obj_ref][$lang_id])) {
			$this->loaded[$obj_ref][$lang_id] = array();
		}

		$this->loaded[$obj_ref][$lang_id][$rec->prop_name] = $rec;
	}


	/**
	 * Mark an object for preloading
	 *
	 * @param int|\Application\DeskPRO\Entity\Language $lang
	 * @param object $object
	 */
	public function preloadObject($lang, $object)
	{
		$lang_id = is_object($lang) ? $lang->getId() : $lang;
		$obj_ref = is_object($object) ? $object->getObjectRef() : $object;

		if (isset($this->loaded[$obj_ref])) {
			return;
		}

		if (!isset($this->queued_objects[$lang_id])) {
			$this->queued_objects[$lang_id] = array();
		}

		$this->queued_objects[$lang_id][$obj_ref] = $obj_ref;
	}


	/**
	 * @param int|\Application\DeskPRO\Entity\Language $lang
	 * @param array $collection
	 */
	public function preloadObjectCollection($lang, $collection)
	{
		foreach ($collection as $obj) {
			$this->preloadObject($lang, $obj);
		}
	}


	/**
	 * Exec the query that fetches any langs we have queued up
	 */
	public function runPreload()
	{
		if (!$this->queued_objects) {
			return;
		}

		$run = $this->queued_objects;
		$this->queued_objects = array();

		foreach ($run as $lang_id => $refs) {
			$recs = $this->em->createQuery("
				SELECT o
				FROM DeskPRO:ObjectLang o
				WHERE o.ref IN (?0) AND o.language = ?1
			")->setParameters(array(array_values($refs), $lang_id))->execute();

			foreach ($recs as $rec) {
				$obj_ref = $rec->ref;
				if (!isset($this->loaded[$obj_ref])) {
					$this->loaded[$obj_ref] = array();
				}
				if (!isset($this->loaded[$obj_ref][$lang_id])) {
					$this->loaded[$obj_ref][$lang_id] = array();
				}

				$this->loaded[$obj_ref][$lang_id][$rec->getPropName()] = $rec;
			}
		}
	}


	/**
	 * Clear the loaded lang objects
	 *
	 * @param null $object Optionally only clear this one object
	 */
	public function clear($object = null)
	{
		if ($object) {
			$ref = $object->getObjectRef();
			unset($this->loaded[$ref]);
		} else {
			$this->loaded = array();
		}
	}
}