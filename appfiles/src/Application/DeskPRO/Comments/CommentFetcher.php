<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Comments
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Comments;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Arrays;

use \Symfony\Component\Form;

class CommentFetcher
{
	protected $em;

	public function __construct($em)
	{
		$this->em;
	}


	public function getPublicComments($object)
	{
		return $this->em->getEntityManager()->createQuery("
			SELECT c
			FROM $table c
			WHERE c.status = ?1 AND c.{$field} = ?
			ORDER BY c.id DESC
		")->setParameter(1, 'visible')->setParameter(2, $object)->execute();
	}
}