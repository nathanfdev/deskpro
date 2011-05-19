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

class ArticleValidatingEdit extends EntityRepository
{
	public function getValidatingEdit()
	{
		$validating_edits = $this->getEntityManager()->createQuery("
			SELECT e, a
			FROM DeskPRO:ArticleValidatingEdit e
			LEFT JOIN e.article a
			ORDER BY e.id DESC
		")->execute();

		return $validating_edits;
	}
}