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

namespace Application\DeskPRO\Domain;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\ObjectLang;
use Doctrine\ORM\Mapping\ClassMetadata;

class ObjectTranslatable
{
	protected $em;

	/**
	 * @var DomainObject
	 */
	protected $entity;

	/**
	 * @var array
	 */
	protected $config;

	/**
	 * @var \Application\DeskPRO\Translate\ObjectLangRepository
	 */
	protected $obj_lang_repos;

	/**
	 * @var \Application\DeskPRO\Entity\Language
	 */
	protected $lang = null;

	/**
	 * @var array
	 */
	protected $unsaved = array();

	public function __construct(DomainObject $entity, $config)
	{
		$this->entity = $entity;
		$this->config = $config;

		$this->em = App::getOrm();
		$this->obj_lang_repos = App::getSystemService('object_lang_repository');
		$this->lang = App::getContainer()->getDataService('Language')->getDefault();
	}

	/**
	 * @param string $prop
	 * @return null
	 */
	public function getObjectProp($prop)
	{
		if (!$this->entity->getId()) {
			$prop = strtolower($prop);
			$lang_id = $this->lang->getId();
			return isset($this->unsaved[$lang_id][$prop]) ? $this->unsaved[$lang_id][$prop]->text : null;
		}

		return $this->obj_lang_repos->get($this->lang, $this->entity, $prop);
	}


	/**
	 * @param string $prop
	 * @param string $value
	 */
	public function setObjectProp($prop, $value)
	{
		if (!$this->entity->getId()) {
			$prop = strtolower($prop);
			$lang_id = $this->lang->getId();

			$rec = isset($this->unsaved[$lang_id][$prop]) ? $this->unsaved[$lang_id][$prop] : null;
			if (!$rec) {
				$rec = ObjectLang::createObjectLang($this->lang, $this->entity, $prop, $value);
			}
			$rec->text = $value;

			if (!isset($this->unsaved[$lang_id])) {
				$this->unsaved[$lang_id] = array();
			}
			$this->unsaved[$lang_id][$prop] = $rec;
			return $rec;
		}

		return $this->obj_lang_repos->setRec($this->lang, $this->entity, $prop, $value);
	}


	####################################################################################################################

	public function _dynGetObjectProp($flags, $call_args)
	{
		return $this->getObjectProp($flags['property']);
	}

	public function _dynSetObjectProp($flags, $call_args)
	{
		return $this->setObjectProp($flags['property'], $call_args[0]);
	}

	public function _dynSetLanguage($flags, $call_args)
	{
		$lang = $call_args[0];
		if (!is_object($lang)) {
			$lang = App::getContainer()->getDataService('Language')->getDefault();
		}

		if (!$lang || !($lang instanceof Language)) {
			throw new \InvalidArgumentException("Invalid language");
		}

		$this->lang = $lang;
	}

	public function _dpTranslatePersistChanges()
	{
		if ($this->unsaved) {
			foreach ($this->unsaved as $group) {
				foreach ($group as $rec) {
					$this->em->delayedInsert($rec);
				}
			}
			$this->unsaved = array();
		}

		if ($this->entity->getId()) {
			foreach ($this->obj_lang_repos->getLoadedRecs($this->entity) as $rec) {
				$this->em->persist($rec);
			}
		}
	}

	####################################################################################################################

	public static function loadObjectTranslatable(DomainObject $object)
	{
		if (isset($object->_dp_object_translatable) && $object->_dp_object_translatable) {
			return $object->_dp_object_translatable;
		}

		$config = $object::loadObjectTranslatableMetadata();
		$object->_dp_object_translatable = new self($object, $config);

		foreach ($config['fields'] as $f) {
			$fl = strtolower($f);
			$object->addCustomCallable("get$fl", array($object->_dp_object_translatable, '_dynGetObjectProp'), array('property' => $f));
			$object->addCustomCallable("set$fl", array($object->_dp_object_translatable, '_dynSetObjectProp'), array('property' => $f));
		}
		$object->addCustomCallable("setTranslateLanguage", array($object->_dp_object_translatable, '_dynSetLanguage'));

		return $object->_dp_object_translatable;
	}

	/**
	 * @param ClassMetadata $metadata
	 */
	public static function loadEntityMetadata(ClassMetadata $metadata)
	{
		$metadata->addLifecycleCallback('getObjectTranslatable', 'postLoad');
	}
}