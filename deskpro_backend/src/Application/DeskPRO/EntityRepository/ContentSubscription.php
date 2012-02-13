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
use Application\DeskPRO\Entity\Download as DownloadEntity;
use Application\DeskPRO\Entity\News as NewsEntity;
use Application\DeskPRO\Entity\Feedback as FeedbackEntity;

use \Doctrine\ORM\EntityRepository;

class ContentSubscription extends EntityRepository
{
	/**
	 * Get a subscription for a type of content
	 *
	 * @param $content_object An Article, Feedback, News or Download
	 * @param \Application\DeskPRO\Entity\Person $person
	 * @return \Application\DeskPRO\Entity\ContentSubscription
	 */
	public function getSubscription($content_object, PersonEntity $person)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('s');
		$qb->from('DeskPRO:ContentSubscription', 's');
		$qb->andWhere("s.person = ?1");

		if ($content_object instanceof ArticleEntity) {
			$qb->andWhere("s.article = ?2");
		} elseif ($content_object instanceof DownloadEntity) {
			$qb->andWhere("s.download = ?2");
		} elseif ($content_object instanceof NewsEntity) {
			$qb->andWhere("s.news = ?2");
		} elseif ($content_object instanceof FeedbackEntity) {
			$qb->andWhere("s.feedback = ?2");
		} else {
			throw new \InvalidArgumentException("\$content_object must be Article, Download, News or Feedback. Got `" . get_class($content_object) . "`");
		}

		$qb->setParameters(array(1 => $person, 2 => $content_object));

		try {
			return $qb->getQuery()->getSingleResult();
		} catch (\Exception $e) {
			return null;
		}
	}

	public function getSubscriptionsForPerson(PersonEntity $person)
	{
		return $this->getEntityManager()->createQuery("
			SELECT s
			FROM DeskPRO:ContentSubscription s
			WHERE s.person = ?1
		")->execute(array(1 => $person));
	}
}
