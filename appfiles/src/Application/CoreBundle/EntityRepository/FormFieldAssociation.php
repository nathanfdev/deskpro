<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\CoreBundle\EntityRepository;

use \Doctrine\ORM\EntityRepository;

class FormFieldAssociation extends EntityRepository
{
	/**
	 * Array of systype=>array(field ids)
	 * @var array
	 */
	protected $fields_for_systype = null;



	/**
	 * get an array of fields for the specified IDs.
	 * 
	 * @param array $ids
	 * @return array
	 */
	public function getFieldsWithIds(array $ids)
	{
		$fields = $this->_em->createQuery("
			SELECT f
			FROM CoreBundle:FormField f
			WHERE f.id IN ?1
		")->setParameter(1, $ids)->getResults();

		return $fields;
	}
	


	/**
	 * Get an array of field entities that apply for some type.
	 *
	 * TODO: handle default on kind of cases? some fields arent always showed etc
	 *
	 * @param string $systype
	 * @return array
	 */
	public function getFieldsForType($systype)
	{
		$ids = $this->getFieldIdsForType($systype);

		if (!$ids) {
			return array();
		}

		return $this->getFieldsWithIds($ids);
	}


	/**
	 * Get an array of field ID's that apply for some type.
	 * 
	 * @param string $systype
	 * @return array
	 */
	public function getFieldIdsForType($systype)
	{
		if ($this->fields_for_systype === null) {
			$this->_initFieldsForSystypeArray();
		}

		return isset($this->fields_for_systype[$systype]) ? $this->fields_for_systype[$systype] : null;
	}

	protected function _initFieldsForSystypeArray()
	{
		$this->fields_for_systype = array();

		/* @var DeskPRO\DBAL\Connection */
		$db = $this->_em->getConnection();

		$rows = $db->fetchAll("
			SELECT form_field_id, systype
			FROM form_field_associations
		");
		foreach ($rows as $row) {
			if (!isset($this->fields_for_systype[$row['systype']])) {
				$this->fields_for_systype[$row['systype']] = array();
			}

			$this->fields_for_systype[$row['systype']][] = $row['form_field_id'];
		}

		return $rows;
	}
}