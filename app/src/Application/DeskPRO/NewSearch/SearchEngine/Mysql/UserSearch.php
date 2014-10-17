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

namespace Application\DeskPRO\NewSearch\SearchEngine\Mysql;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\NewSearch\SearchEngine\Result\ResultSet;
use Application\DeskPRO\NewSearch\SearchEngine\SearchContext;
use Application\DeskPRO\NewSearch\SearchEngine\SearchContextInterface;
use Application\DeskPRO\NewSearch\SearchEngine\UserSearchInterface;
use Doctrine\DBAL\Query\QueryBuilder;
use Elastica\Filter;
use Elastica\Query;
use Orb\Util\Arrays;
use Orb\Util\Numbers;
use Orb\Util\OptionsArray;

class UserSearch implements UserSearchInterface
{
	/**
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	private $db;

	/**
	 * @var MysqlResultsTransformer
	 */
	private $transformer;


	/**
	 * @param Connection              $db
	 * @param MysqlResultsTransformer $transformer
	 */
	public function __construct(Connection $db, MysqlResultsTransformer $transformer)
	{
		$this->db = $db;
		$this->transformer = $transformer;
	}


	/**
	 * @param SearchContextInterface $context
	 * @param string                 $query
	 * @param array                  $options
	 * @return ResultSet
	 */
	public function search(SearchContextInterface $context, $query, array $options = null)
	{
		$options      = new OptionsArray($options ?: array());
		$per_page     = Numbers::bound($options->get('per_page', 50), 1, 100);
		$page         = max($options->get('page', 1), 1);
		$ignore_perms = $options->get('ignore_perms');

		$limit_types = isset($options['limit_types']) ? $options['limit_types'] : null;
		if ($limit_types && !is_array($limit_types)) {
			$limit_types = explode(',', $limit_types);
			$limit_types = Arrays::func($limit_types, 'trim');
		}
		if ($limit_types) {
			$limit_types = Arrays::removeFalsey($limit_types);
		}

		$limit_types_array = $limit_types;

		$context_params = $this->buildParams($context, $limit_types);
		$types = $context_params['types'];

		if (!$types) {
			return new ResultSet(array());
		}

		$limit_types = "'" . implode('\',\'', $types) . "'";

		$query_words = explode(' ', $query);
		if (!$query_words) {
			return new ResultSet();
		}

		$qb = $this->db->createQueryBuilder()
			->from('content_search', 'cs')
			->where('cs.object_type IN (:types)')
			->setParameter(':types', $types, Connection::PARAM_STR_ARRAY);

		foreach ($query_words as $k => $w) {
			if (strlen($w) <= 2) {
				continue;
			}

			$key = ':where' . $k;
			$val = '%' . str_replace(array('%', '_', '\\'), array('\\%', '\\_', '\\\\'), $w) . '%';
			$qb
				->andWhere('cs.content LIKE ' . $key)
				->setParameter($key, $val);
		}

		// the first parameter is :types
		if (count($qb->getParameters()) > 1) {

			if (!$ignore_perms) {
				$this->buildPermsQuery($context, $qb, $types);
			}

			$per_page && $qb->setMaxResults($per_page);
			$total = $qb->select('COUNT(*)')->execute()->fetchColumn();

			$page > 0 && $per_page && $qb->setFirstResult(($page - 1) * $per_page);
			$qb->orderBy('cs.object_id', 'DESC');
			$results = $qb->select('cs.object_type, cs.object_id')->execute()->fetchAll();

		} else {
			$total       = 0;
			$results     = array();
		}

		if ($total === null) {
			$total = count($results);
		}

		if ($context->getPerson() && ($limit_types_array === null || in_array('ticket', $limit_types_array)) && $page == 1) {
			$ticket_results = $this->getTicketResults($context, $query_words);

			if ($ticket_results) {
				$total += count($ticket_results);
				$results = array_merge($ticket_results, $results);
			}
		}

		$objects = $this->transformer->transform($results);

		return new ResultSet($objects, $total);
	}

	/**
	 * add permissions part to query
	 * @param SearchContext $context
	 * @param QueryBuilder $qb
	 * @param array $types
	 */
	protected function buildPermsQuery(SearchContext $context, QueryBuilder $qb, array $types)
	{
		$qb->join('cs', 'content_search_attribute', 'csa', '
			csa.object_type = cs.object_type
			AND csa.object_id = cs.object_id
			AND csa.attribute_id LIKE "category_id%"
		');

		$joinWhere = array();
		foreach ($types as $type) {
			$method = 'get' . ucfirst($type) . 'CategoryIds';

			if (!is_callable(array($context, $method))) continue;
			if (!$allowed = $context->$method()) continue;

			$joinWhere[] = "(csa.object_type = '$type' AND csa.content IN (:allowed_$type))";
			$qb->setParameter('allowed_' . $type, $allowed, Connection::PARAM_INT_ARRAY);
		}

		$qb->andWhere('(' . implode(' OR ', $joinWhere) . ')');
	}

	private function getTicketResults(SearchContextInterface $context, array $query_words)
	{
		$limit = 5;

		$search_places = array();
		$search_params = array();

		foreach ($query_words as $w) {
			if (strlen($w) <= 2) {
				continue;
			}

			$search_places[] = "tickets_messages.message LIKE ?";
			$search_params[] = '%' . str_replace(array('%', '_', '\\'), array('\\%', '\\_', '\\\\'), $w) . '%';
		}

		if (!$search_params) {
			return array();
		}

		$search_places = implode(' OR ', $search_places);

		if ($context->getPerson()->organization && $context->getPerson()->organization_manager) {
			$params = array(
				$context->getPerson()->getId(),
				$context->getPerson()->getId(),
				$context->getPerson()->organization->getId()
			);

			$params = array_merge($params, $search_params);

			$ticket_ids = $this->db->fetchAllCol("
				SELECT tickets.id
				FROM tickets
				LEFT JOIN tickets_participants ON (tickets_participants.ticket_id = tickets.id)
				LEFT JOIN tickets_messages ON (tickets_messages.ticket_id = tickets.id AND tickets_messages.is_agent_note = 0)
				WHERE
					(tickets.person_id = ?
					OR tickets_participants.person_id = ?
					OR tickets.organization_id = ?)
					AND ($search_places)
				ORDER BY tickets.date_status DESC, tickets.date_created DESC
				LIMIT $limit
			", $params);
		} else {
			$params = array(
				$context->getPerson()->getId(),
				$context->getPerson()->getId(),
			);

			$params = array_merge($params, $search_params);

			$ticket_ids = $this->db->fetchAllCol("
				SELECT tickets.id
				FROM tickets
				LEFT JOIN tickets_participants ON (tickets_participants.ticket_id = tickets.id)
				LEFT JOIN tickets_messages ON (tickets_messages.ticket_id = tickets.id AND tickets_messages.is_agent_note = 0)
				WHERE
					(tickets.person_id = ?
					OR tickets_participants.person_id = ?)
					AND ($search_places)
				ORDER BY tickets.date_status DESC, tickets.date_created DESC
				LIMIT $limit
			", $params);
		}

		if (!$ticket_ids) {
			return array();
		}

		$hits = array_map(function($tid) {
			return array(
				'object_type' => 'ticket',
				'object_id'   => $tid,
			);
		}, $ticket_ids);

		return $hits;
	}

	/**
	 * @param SearchContextInterface $context
	 * @param array $limit_types
	 * @return ResultSet
	 */
	private function buildParams(SearchContextInterface $context, array $limit_types = null)
	{
		$types  = array();
		$joins  = array();
		$wheres = array();

		$x = 0;
		if ($context->getArticleCategoryIds() && ($limit_types === null || in_array('article', $limit_types))) {
			$jn = '_cs' . $x++;
			$cat_ids = implode(',', $context->getArticleCategoryIds());

			$types[]  = 'article';
			$joins[]  = "LEFT JOIN content_search_attribute AS $jn ON ($jn.object_type = 'article' AND $jn.object_type = content_search.object_type AND $jn.object_id = content_search.object_id AND $jn.attribute_id LIKE 'category_id%' AND $jn.content IN ($cat_ids))";
			$wheres[] = "($jn.object_type = 'article' AND $jn.object_id IS NOT NULL)";
		}
		if ($context->getNewsCategoryIds() && ($limit_types === null || in_array('news', $limit_types))) {
			$jn = '_cs' . $x++;
			$cat_ids = implode(',', $context->getNewsCategoryIds());

			$types[]  = 'news';
			$joins[]  = "LEFT JOIN content_search_attribute AS $jn ON ($jn.object_type = 'news' AND $jn.object_type = content_search.object_type AND $jn.object_id = content_search.object_id AND $jn.attribute_id = 'category_id' AND $jn.content IN ($cat_ids))";
			$wheres[] = "($jn.object_type = 'news' AND $jn.object_id IS NOT NULL)";
		}
		if ($context->getFeedbackCategoryIds() && ($limit_types === null || in_array('feedback', $limit_types))) {
			$jn = '_cs' . $x++;
			$cat_ids = implode(',', $context->getFeedbackCategoryIds());

			$types[]  = 'feedback';
			$joins[]  = "LEFT JOIN content_search_attribute AS $jn ON ($jn.object_type = 'feedback' AND $jn.object_type = content_search.object_type AND $jn.object_id = content_search.object_id AND $jn.attribute_id = 'category_id' AND $jn.content IN ($cat_ids))";;
			$wheres[] = "($jn.object_type AND $jn.object_id IS NOT NULL)";
		}
		if ($context->getDownloadCategoryIds() && ($limit_types === null || in_array('download', $limit_types))) {
			$jn = '_cs' . $x++;
			$cat_ids = implode(',', $context->getDownloadCategoryIds());

			$types[]  = 'download';
			$joins[]  = "LEFT JOIN content_search_attribute AS $jn ON ($jn.object_type = 'download' AND $jn.object_type = content_search.object_type AND $jn.object_id = content_search.object_id AND $jn.attribute_id = 'category_id' AND $jn.content IN ($cat_ids))";
			$wheres[] = "($jn.object_type = 'download' AND $jn.object_id IS NOT NULL)";
		}

		return array(
			'types' => $types,
			'join'  => implode("\n", $joins),
			'where' =>  "(" . implode(' OR ', $wheres) . ")"
		);
	}
}