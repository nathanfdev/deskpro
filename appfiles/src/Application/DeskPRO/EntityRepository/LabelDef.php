<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EntityRepository;

use \Application\DeskPRO\App;
use \Doctrine\ORM\EntityRepository;

class LabelDef extends EntityRepository
{
	/**
	 * Get the top counts for labels of a certain type.
	 *
	 * @return array
	 */
	public function getLabelCounts($type, $limit = 25)
	{
		// TODO this should be cached somehow, or probably
		// needs new column in LaeblDef to store counts statically, and then
		// add postInsert code to each label entity to increase the count automatically

		switch ($type) {
			case 'tickets':
			case 'ticket':
				return $this->getEntityManager()->getConnection()->fetchAllKeyValue("
					SELECT label, COUNT(*) AS count
					FROM labels_tickets
					GROUP BY label
					ORDER BY count DESC
					" . ($limit ? "LIMIT $limit" : '') . "
				");
				break;

			case 'people':
				return $this->getEntityManager()->getConnection()->fetchAllKeyValue("
					SELECT label, COUNT(*) AS count
					FROM labels_people
					GROUP BY label
					ORDER BY count DESC
					" . ($limit ? "LIMIT $limit" : '') . "
				");
				break;

			case 'organizations':
				return $this->getEntityManager()->getConnection()->fetchAllKeyValue("
					SELECT label, COUNT(*) AS count
					FROM labels_organizations
					GROUP BY label
					ORDER BY count DESC
					" . ($limit ? "LIMIT $limit" : '') . "
				");
				break;

			default:
				throw new \InvalidArgumentException("`$type` is an invlaid label type");
				break;
		}
	}

	/**
	 * Get the name of the label entity given a type.
	 *
	 * @static
	 * @param  $label_type
	 * @return null|string
	 */
	public function getLabelEntityFromType($label_type)
	{
		switch ($label_type) {
			case 'organizations':
				return 'DeskPRO:LabelOrganization';
				break;

			case 'people':
				return 'DeskPRO:LabelPerson';
				break;

			case 'tickets':
				return 'DeskPRO:LabelTicket';
				break;
		}

		return null;
	}

	/**
	 * A tablename=>entityname array of objects that have label capabiltiies.
	 *
	 * @static
	 * @return array
	 */
	public function getLabelEntities()
	{
		return array(
			'labels_organizations' => 'DeskPRO:LabelOrganization',
			'labels_people'        => 'DeskPRO:LabelPerson',
			'labels_tickets'       => 'DeskPRO:LabelTicket',
		);
	}
}