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

namespace Application\DeskPRO\EntityRepository;

use \Application\DeskPRO\App;

use \Doctrine\ORM\EntityRepository;

class PersonField extends EntityRepository
{
	/**
	 * @return array
	 */
	public function getEnabledFields()
	{
		return $this->_em->createQuery("
			SELECT f
			FROM DeskPRO:PersonField f
			WHERE f.parent IS NULL
		")->execute();
	}
}