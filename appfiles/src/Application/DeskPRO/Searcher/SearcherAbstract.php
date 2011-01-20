<?php

namespace Application\DeskPRO\Searcher;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;
use \Orb\Util\Strings;
use \Orb\Util\Arrays;

abstract class SearcherAbstract
{
	const OP_IS          = 'is';
	const OP_NOT         = 'not';
	const OP_LT          = 'lt';
	const OP_GT          = 'gt';
	const OP_LTE         = 'lte';
	const OP_GTE         = 'gte';
	const OP_CONTAINS    = 'contains';
	const OP_NOTCONTAINS = 'notcontains';
	const OP_NOOP        = null;

	const ORDER_ASC      = 'ASC';
	const ORDER_DESC     = 'DESC';

	/**
	 * The person context
	 * @var Entity\Person
	 */
	protected $person = array();

	/**
	 * Array of terms we've set.
	 * term_type=>array(op_type, choice)
	 * 
	 * @var array
	 */
	protected $terms = array();

	/**
	 * array(type, direction) of ordering
	 * @var array
	 */
	protected $order_by = array();


	
	/**
	 * Set the person context to fetch permissions etc form
	 * 
	 * @param Entity\Person $person 
	 */
	public function setPerson(Entity\Person $person)
	{
		$this->person = $person;
	}


	
	/**
	 * Set an array of terms at once.
	 *
	 * @param array $terms
	 */
	public function setTerms(array $terms)
	{
		$this->terms = array_merge($this->terms, $terms);
	}


	
	/**
	 * Set the ordering
	 *
	 * @param string $type
	 * @param string $direction
	 */
	public function setOrderBy($type, $direction = self::ORDER_DESC)
	{
		$this->order_by = array($type, $direction);
	}


	
	/**
	 * Set orderBy using a 'code' which is "type:direction" such as "ticket.id:asc".
	 *
	 * @param string $order_by_code
	 */
	public function setOrderByCode($order_by_code)
	{
		if (strpos($order_by_code, ':') === false) {
			$order_by_code .= ':' . self::ORDER_DESC;
		}

		list($type, $direction) = explode(':', $order_by_code);

		$this->setOrderBy($type, $direction);
	}



	/**
	 * Add a new term.
	 *
	 * @param  $term
	 * @param  $op
	 * @param  $data
	 */
	public function addTerm($term, $op, $data)
	{
		$this->terms[$term] = array($op, $data);
	}



	/**
	 * Run the search and return an array of matching ID's.
	 *
	 * @param int $limit
	 * @return array
	 */
	abstract public function getMatches();


	/**
	 * Build a "where" part on simple fields given a choice.
	 *
	 * @param  $field
	 * @param  $op
	 * @param  $choice
	 * @return string
	 */
	protected function _choiceMatch($field, $op, $choice)
	{
		$db = App::getDb();
		$where = '';

		if (is_array($choice)) {

			$choices_in = $choice;
			array_walk($choices_in, function($v, $k) use ($db) {
				$v = $db->quote($v);
			});

			$choices_in = "(" . implode(',', $choices_in) . ")";

			if ($op == self::OP_CONTAINS OR $op == self::OP_IS) {
				$where = "$field IN $choices_in";
			} elseif ($op == self::OP_NOTCONTAINS OR $op == self::OP_NOT) {
				$where = "$field NOT IN $choices_in";
			}
		} else {
			if ($choice === 0 OR $choice === '0') {
				$choice = 'NULL';
				$op = ($op == self::OP_IS) ? "IS" : "IS NOT";
			}
			else {
				$choice = $db->quote($choice);
				$op = ($op == self::OP_IS) ? "=" : "!=";
			}

			$where = "$field $op $choice";
		}

		return $where;
	}
}