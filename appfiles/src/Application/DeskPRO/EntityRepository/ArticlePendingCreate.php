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

class ArticlePendingCreate extends EntityRepository
{
	public function getPendingArticles()
	{
		$pending_articles = $this->getEntityManager()->createQuery("
			SELECT a, t, p
			FROM DeskPRO:ArticlePendingCreate a
			LEFT JOIN a.ticket t
			LEFT JOIN a.person p
			ORDER BY a.date_created
		")->execute();

		return $pending_articles;
	}


	/**
	 * Get a list of emails suitable for display
	 */
	public function getList()
	{
		$list = App::getDb()->fetchAllCol("
			SELECT banned_email
			FROM ban_emails
			ORDER BY banned_email ASC
		");

		return $list;
	}
}