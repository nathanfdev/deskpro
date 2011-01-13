<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Filter
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Filter;

class FilterChain extends AbstractFilter implements \Countable, \IteratorAggregate
{
	/**
	 * An array of filters
	 * @var array
	 */
	protected $_filters = array();


	
	/**
	 * Add a new filter to the chain
	 *
	 * @param \Zend\Filter\Filter $filter
	 */
	public function addFilter(\Zend\Filter\Filter $filter)
	{
		$this->_filters[] = $filter;
	}



	/**
	 * Get the filters currently set.
	 * 
	 * @return array
	 */
	public function getFilters()
	{
		return $this->_filters;
	}


	
	/**
	 * Filter a value.
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public function filter($value)
	{
		foreach ($this->_filters as $filter) {
			$value = $filter->filter($value);
		}

		return $value;
	}


	
	/**
	 * Count how many filters there are.
	 *
	 * @return int
	 */
	public function count()
	{
		return count($this->_filters);
	}



	/**
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->_filters);
	}
}