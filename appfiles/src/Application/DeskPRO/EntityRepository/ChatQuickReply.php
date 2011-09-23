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

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person as PersonEntity;

use \Doctrine\ORM\EntityRepository;

class ChatQuickReply extends EntityRepository
{
	public function getRepliesForPerson(PersonEntity $person)
	{
		// TODO sort out permissions etc

		return $this->findAll();
	}
}
