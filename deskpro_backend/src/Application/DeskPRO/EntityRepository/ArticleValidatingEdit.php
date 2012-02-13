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
use Application\DeskPRO\Entity\Article as ArticleEntity;

use Doctrine\ORM\EntityRepository;

class ArticleValidatingEdit extends EntityRepository
{
	public function getEditForArticle(ArticleEntity $article, PersonEntity $person = null)
	{
		try {
			if ($person) {
				$edit = $this->getEntityManager()->createQuery("
					SELECT e
					FROM DeskPRO:ArticleValidatingEdit e
					WHERE e.article = ?1 AND e.person = ?2
				")->setParameter(1, $article)
				  ->setParameter(2, $person)
				  ->getSingleResult();
			} else {
				$edit = $this->getEntityManager()->createQuery("
					SELECT e
					FROM DeskPRO:ArticleValidatingEdit e
					WHERE e.article = ?1
				")->setParameter(1, $article)
				  ->getSingleResult();
			}

			return $edit;
		} catch (\Exception $e) {
			return null;
		}
	}

	public function getValidatingEdit()
	{
		$validating_edits = $this->getEntityManager()->createQuery("
			SELECT e, a
			FROM DeskPRO:ArticleValidatingEdit e
			LEFT JOIN e.article a
		")->execute();

		return $validating_edits;
	}
}