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

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Filters;

use Application\DeskPRO\Tickets\Filters\Terms\FilterQuery;

class TicketQueryBuilder
{
	/**
	 * @param array $fields
	 * @param FilterQuery $query
	 * @return array array('sql' => '...', 'params' => array(...))
	 */
	public function getSelectQuery(array $fields, FilterQuery $query)
	{
		$parts = $query->getQueryParts();

		$query = "SELECT " . implode(', ', $fields) . "\n";
		$query .= "FROM tickets\n";

		$join_map = array();
		if (!empty($parts['joins'])) {
			$count = 0;
			foreach ($parts['joins'] AS $join) {
				$count++;
				$alias = $join['alias'] ? $join['alias'] : $join['input_alias'];
				$query .= "LEFT JOIN {$join['join']} AS $alias ON ({$join['condition']})\n";
				if ($join['alias']) {
					$join_map[$join['alias']] = 'join'.$count;
				}
			}
		}
		$query .= "WHERE\n" . $parts['where'];

		$param_map = array();
		foreach ($parts['params'] as $p) {
			$param_map[$p['name']] = $p['value'];
		}

		$ret_params = array();
		$query = preg_replace_callback('#__dpparam_.*?__#', function($m) use (&$ret_params, $param_map) {
			$ret_params[] = $param_map[$m[0]];
			return '?';
		}, $query);
		$query = preg_replace_callback('#__dpjoin_.*?__#', function($m) use ($join_map) {
			return $join_map[$m[0]];
		}, $query);

		return array(
			'sql'    => $query,
			'params' => $ret_params,
		);
	}


	/**
	 * @param FilterQuery $query
	 * @return array array('sql' => '...', 'params' => array(...))
	 */
	public function getCountQuery(FilterQuery $query)
	{
		$parts = $query->getQueryParts();

		$query = "SELECT COUNT(*)\n";
		$query .= "FROM tickets\n";

		$join_map = array();
		if (!empty($parts['joins'])) {
			$count = 0;
			foreach ($parts['joins'] AS $join) {
				$count++;
				$alias = $join['alias'] ? $join['alias'] : $join['input_alias'];
				$query .= "LEFT JOIN {$join['join']} AS $alias ON ({$join['condition']})\n";
				if ($join['alias']) {
					$join_map[$join['alias']] = 'join'.$count;
				}
			}
		}
		$query .= "WHERE\n" . $parts['where'];

		$param_map = array();
		foreach ($parts['params'] as $p) {
			$param_map[$p['name']] = $p['value'];
		}

		$ret_params = array();
		$query = preg_replace_callback('#__dpparam_.*?__#', function($m) use (&$ret_params, $param_map) {
			$ret_params[] = $param_map[$m[0]];
			return '?';
		}, $query);
		$query = preg_replace_callback('#__dpjoin_.*?__#', function($m) use ($join_map) {
			return $join_map[$m[0]];
		}, $query);

		return array(
			'sql'    => $query,
			'params' => $ret_params,
		);
	}
}