<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Filter
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Filter;

class FilterChain extends \Zend\Filter\AbstractFilter
{
	/**
	 * An array of filters
	 * @var array
	 */
	protected $filters = array();


	
	/**
	 * Add a new filter to the chain
	 *
	 * @param \Zend\Filter\Filter $filter
	 */
	public function addFilter(\Zend\Filter\Filter $filter)
	{
		$this->filters[] = $filter;
	}



	/**
	 * Get the filters currently set.
	 * 
	 * @return array
	 */
	public function getFilters()
	{
		return $this->filters;
	}


	
	/**
	 * Filter a value.
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public function filter($value)
	{
		foreach ($this->filters as $filter) {
			$value = $filter->filter($value);
		}

		return $value;
	}
}