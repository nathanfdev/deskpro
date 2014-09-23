<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
		$per_page     = $options->get('per_page');
		$page         = $options->get('page');
		$ignore_perms = $options->get('ignore_perms');

		$types = array('article', 'download', 'feedback', 'news');

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
}