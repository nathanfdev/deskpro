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
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Filters\Terms;

use Application\DeskPRO\Tickets\ExecutorContextInterface;

class FilterTermComposite implements FilterTermInterface
{
	const OP_AND = 'AND';
	const OP_OR  = 'OR';

	/**
	 * @var FilterTermInterface[]
	 */
	private $terms = array();

	/**
	 * @var string
	 */
	private $op = 'AND';


	/**
	 * @param FilterTermInterface[] $terms
	 * @param string $op
	 */
	public function __construct(array $terms = array(), $op = self::OP_AND)
	{
		$this->setAll($terms);
		$this->setOperator($op);
	}


	/**
	 * Change the logic operator between AND/OR ('all must match' versus 'any match')
	 *
	 * @param string $op
	 */
	public function setOperator($op)
	{
		$this->op = (strtoupper($op) == self::OP_AND ? self::OP_AND : self::OP_OR);
	}


	/**
	 * @return string
	 */
	public function getOperator()
	{
		return $this->op;
	}


	/**
	 * @param FilterTermInterface $term
	 */
	public function add(FilterTermInterface $term)
	{
		$this->terms[] = $term;
	}


	/**
	 * @param FilterTermInterface[] $terms
	 */
	public function setAll(array $terms)
	{
		$this->terms = array();
		foreach ($terms as $t) {
			$this->add($t);
		}
	}


	/**
	 * @return FilterTermInterface[]
	 */
	public function getAll()
	{
		return $this->terms;
	}


	/**
	 * @param ExecutorContextInterface $context
	 * @return FilterQuery|null
	 */
	public function getFilterQuery(ExecutorContextInterface $context = null)
	{
		$filter_query = new FilterQuery();

		foreach ($this->terms as $t) {
			$query = $t->getFilterQuery();
			if (!$query) {
				continue;
			}

			$parts = $query->getQueryParts();
			foreach ($parts['joins'] as $join) {
				$filter_query->addJoin(
					$join['fromAlias'],
					$join['join'],
					$join['alias'] ?: $join['input_alias'],
					$join['condition']
				);
			}
			foreach ($parts['params'] as $param) {
				$filter_query->setParameter(
					$param['name'],
					$param['value'],
					$param['type'],
					false
				);
			}

			if ($this->op == self::OP_AND) {
				$filter_query->andWhere($parts['where']);
			} else {
				$filter_query->orWhere($parts['where']);
			}
		}

		return $filter_query;
	}
}