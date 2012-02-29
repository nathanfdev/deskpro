<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Labels
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
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
	public function search($label)
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
				SELECT labels_tickets.ticket_id
				FROM labels_tickets
				LEFT JOIN tickets ON tickets.id = labels_tickets.ticket_id
				WHERE labels_tickets.label = ? AND tickets.status IN ('awaiting_agent', 'awaiting_user', 'closed', 'resolved')
				ORDER BY labels_tickets.ticket_id DESC
				LIMIT {$this->limit}
			", array($label));

			if ($ids) {
				$results['ticket'] = $this->em->getRepository('DeskPRO:Ticket')->getByIds($ids, true);
			}
		}

		if (in_array('person', $this->search_types)) {
			$ids = $this->db->fetchAllCol("
				SELECT labels_people.person_id
				FROM labels_people
				WHERE labels_people.label = ?
				ORDER BY labels_people.person_id DESC
				LIMIT {$this->limit}
			", array($label));

			if ($ids) {
				$results['person'] = $this->em->getRepository('DeskPRO:Person')->getByIds($ids, true);
			}
		}

		if (in_array('organization', $this->search_types)) {
			$ids = $this->db->fetchAllCol("
				SELECT labels_organizations.organization_id
				FROM labels_organizations
				WHERE labels_organizations.label = ?
				ORDER BY labels_organizations.organization_id DESC
				LIMIT {$this->limit}
			", array($label));

			if ($ids) {
				$results['organization'] = $this->em->getRepository('DeskPRO:Organization')->getByIds($ids, true);
			}
		}

		if (in_array('feedback', $this->search_types)) {
			$ids = $this->db->fetchAllCol("
				SELECT labels_feedback.feedback_id
				FROM labels_feedback
				LEFT JOIN feedback ON feedback.id = labels_feedback.feedback_id
				WHERE labels_feedback.label = ? AND (feedback.hidden_status NOT IN('spam', 'deleted') OR feedback.hidden_status IS NULL)
				ORDER BY labels_feedback.feedback_id DESC
				LIMIT {$this->limit}
			", array($label));

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
				LIMIT {$this->limit}
			", array($label));

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
				LIMIT {$this->limit}
			", array($label));

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
				LIMIT {$this->limit}
			", array($label));

			if ($ids) {
				$results['download'] = $this->em->getRepository('DeskPRO:Download')->getByIds($ids, true);
			}
		}

		return $results;
	}
}
