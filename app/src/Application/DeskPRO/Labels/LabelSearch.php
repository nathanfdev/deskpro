<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Labels
 */

namespace Application\DeskPRO\Labels;

use Doctrine\ORM\EntityManager;

class LabelSearch
{
	/**
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	protected $db;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	/**
	 * @var array
	 */
	protected $search_types = array('article', 'download', 'feedback', 'news', 'organization', 'person', 'ticket');

	/**
	 * @var int
	 */
	protected $limit = 15;


	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 */
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->db = $em->getConnection();
	}


	/**
	 * Set the types of things we want to search for
	 *
	 * @param array $types
	 */
	public function setSearchTypes(array $types)
	{
		$this->search_types = $types;
	}


	/**
	 * Set the max number of objects to fetch per type
	 *
	 * @param $limit
	 */
	public function setLimit($limit)
	{
		$this->limit = $limit;
	}


	/**
	 * Do a search and get an array of results
	 *
	 * @param $label
	 */
	public function search($label, $combined = false)
	{
		$results = array(
			'article' => array(),
			'download' => array(),
			'feedback' => array(),
			'news' => array(),
			'ticket' => array(),
			'person' => array(),
			'organization' => array()
		);

		if (in_array('ticket', $this->search_types)) {
			$ids = $this->db->fetchAllCol("
				SELECT ticket_id
				FROM labels_tickets
				WHERE label = ?
				ORDER BY ticket_id DESC
				LIMIT ?
			", array($label, $this->limit), array(\PDO::PARAM_STR, \PDO::PARAM_INT));

			if ($ids) {
				/** @var \Application\DeskPRO\EntityRepository\Ticket $rep */
				$rep = $this->em->getRepository('DeskPRO:Ticket');
				$results['ticket'] = $rep->findBy(
					array('id' => $ids, 'status' => array('awaiting_agent', 'awaiting_user', 'archived', 'resolved')),
					array('id' => 'DESC')
				);
			}
		}

		if (in_array('person', $this->search_types)) {
			$ids = $this->db->fetchAllCol("
				SELECT person_id
				FROM labels_people
				WHERE label = ?
				ORDER BY person_id DESC
				LIMIT ?
			", array($label, $this->limit), array(\PDO::PARAM_STR, \PDO::PARAM_INT));

			if ($ids) {
				/** @var \Application\DeskPRO\EntityRepository\Person $rep */
				$rep = $this->em->getRepository('DeskPRO:Person');
				$results['person'] = $rep->findBy(
					array('id' => $ids),
					array('id' => 'DESC')
				);
			}
		}

		if (in_array('organization', $this->search_types)) {
			$ids = $this->db->fetchAllCol("
				SELECT organization_id
				FROM labels_organizations
				WHERE label = ?
				ORDER BY organization_id DESC
				LIMIT ?
			", array($label, $this->limit), array(\PDO::PARAM_STR, \PDO::PARAM_INT));

			if ($ids) {
				/** @var \Application\DeskPRO\EntityRepository\Organization $rep */
				$rep = $this->em->getRepository('DeskPRO:Organization');
				$results['organization'] = $rep->findBy(
					array('id' => $ids),
					array('id' => 'DESC')
				);
			}
		}

		if (in_array('feedback', $this->search_types)) {
			$ids = $this->db->fetchAllCol("
				SELECT labels_feedback.feedback_id
				FROM labels_feedback
				LEFT JOIN feedback ON feedback.id = labels_feedback.feedback_id
				WHERE labels_feedback.label = ? AND (feedback.hidden_status NOT IN('spam', 'deleted') OR feedback.hidden_status IS NULL)
				ORDER BY labels_feedback.feedback_id DESC
				LIMIT ?
			", array($label, $this->limit), array(\PDO::PARAM_STR, \PDO::PARAM_INT));

			if ($ids) {
				$results['feedback'] = $this->em->getRepository('DeskPRO:Feedback')->getByIds($ids, true);
			}
		}

		if (in_array('article', $this->search_types)) {
			$ids = $this->db->fetchAllCol("
				SELECT labels_articles.article_id
				FROM labels_articles
				LEFT JOIN articles ON articles.id = labels_articles.article_id
				WHERE labels_articles.label = ? AND (articles.hidden_status NOT IN('spam', 'deleted') OR articles.hidden_status IS NULL)
				ORDER BY labels_articles.article_id DESC
				LIMIT ?
			", array($label, $this->limit), array(\PDO::PARAM_STR, \PDO::PARAM_INT));

			if ($ids) {
				$results['article'] = $this->em->getRepository('DeskPRO:Article')->getByIds($ids, true);
			}
		}

		if (in_array('news', $this->search_types)) {
			$ids = $this->db->fetchAllCol("
				SELECT labels_news.news_id
				FROM labels_news
				LEFT JOIN news ON news.id = labels_news.news_id
				WHERE labels_news.label = ? AND (news.hidden_status NOT IN('spam', 'deleted') OR news.hidden_status IS NULL)
				ORDER BY labels_news.news_id DESC
				LIMIT ?
			", array($label, $this->limit), array(\PDO::PARAM_STR, \PDO::PARAM_INT));

			if ($ids) {
				$results['news'] = $this->em->getRepository('DeskPRO:News')->getByIds($ids, true);
			}
		}

		if (in_array('download', $this->search_types)) {
			$ids = $this->db->fetchAllCol("
				SELECT labels_downloads.download_id
				FROM labels_downloads
				LEFT JOIN downloads ON downloads.id = labels_downloads.download_id
				WHERE labels_downloads.label = ? AND (downloads.hidden_status NOT IN('spam', 'deleted') OR downloads.hidden_status IS NULL)
				ORDER BY labels_downloads.download_id DESC
				LIMIT ?
			", array($label, $this->limit), array(\PDO::PARAM_STR, \PDO::PARAM_INT));

			if ($ids) {
				$results['download'] = $this->em->getRepository('DeskPRO:Download')->getByIds($ids, true);
			}
		}

		return $results;
	}
}
