<?php

namespace Application\DeskPRO\Searcher;

use \Application\DeskPRO\App;
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

	/**
	 * Array of terms we've set.
	 * term_type=>array(op_type, choice)
	 * 
	 * @var array
	 */
	protected $terms = array();



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

			if ($op == self::OP_CONTAINS) {
				$where = "$field IN $choices_in";
			} elseif ($op == self::OP_NOTCONTAINS) {
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