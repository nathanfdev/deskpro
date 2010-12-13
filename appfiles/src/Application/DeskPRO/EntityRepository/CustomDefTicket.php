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

class CustomDefTicket extends EntityRepository
{
	/**
	 * @return array
	 */
	public function getFields()
	{
		return $this->_em->createQuery("
			SELECT f
			FROM DeskPRO:CustomDefTicket f
			WHERE f.parent IS NULL
		")->execute();
	}
}