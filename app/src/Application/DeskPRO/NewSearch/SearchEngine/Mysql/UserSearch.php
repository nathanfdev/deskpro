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
use Application\DeskPRO\NewSearch\SearchEngine\SearchContextInterface;
use Application\DeskPRO\NewSearch\SearchEngine\UserSearchInterface;
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

		$limit_type_names = $types;
		$limit_types = "'" . implode('\',\'', $types) . "'";

		$query_words = explode(' ', $query);
		if (!$query_words) {
			return new ResultSet();
		}

		$params = array();
		$likes = array();
		foreach ($query_words as $w) {
			if (strlen($w) <= 2) {
				continue;
			}

			$likes[] = "content_search.content LIKE ?";
			$params[] = '%' . str_replace(array('%', '_', '\\'), array('\\%', '\\_', '\\\\'), $w) . '%';
		}
		if ($likes) {
			$where = "
				content_search.object_type IN ($limit_types)
				AND (" . implode(' OR ', $likes) . ")
			";

			if (!$ignore_perms) {
				$permfilter = new \Application\DeskPRO\Search\Adapter\Mysql\PermissionFilter();
				$permfilter->setPersonContext($this->person);

				if ($limit_type_names) {
					$permfilter->setTypes($limit_type_names);
				}

				$perm_join  = $permfilter->getJoin();
				$perm_where = $permfilter->getWhere();
				if (!$perm_where) {
					$perm_where = '1';
				}
			} else {
				$perm_join = '';
				$perm_where = '1';
			}

			$count_query = "
				SELECT COUNT(*)
				FROM content_search
				$perm_join
				WHERE $perm_where AND $where
				LIMIT $per_page
			";

			$start = ($page - 1) * $per_page;
			$select_query = "
				SELECT content_search.object_type, content_search.object_id
				FROM content_search
				$perm_join
				WHERE $perm_where AND $where
				ORDER BY content_search.object_id DESC
				LIMIT $start, $per_page
			";

			$total = $this->db->fetchColumn($count_query, $params);
			$results  = $this->db->fetchAll($select_query, $params);
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
}