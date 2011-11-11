<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2011 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Abdullah Kiser <kiser.bd@gmail.com>
 */

namespace Application\DeskPRO\EntityRepository;

use Symfony\Component\Validator\Constraints\DateTime;

use Application\DeskPRO\App;
use \Doctrine\ORM\EntityRepository;
use Application\DeskPRO\Entity;
//use Application\DeskPRO\Entity\Deal;

class DealNote extends EntityRepository
{
        public function getNotesForDeal(Deal $deal)
	{
		return $this->getEntityManager()->createQuery("
			SELECT n
			FROM DeskPRO:DealNote n
			WHERE n.deal = ?1
			ORDER BY n.id DESC
		")->execute(array(1=> $deal));
	}
}