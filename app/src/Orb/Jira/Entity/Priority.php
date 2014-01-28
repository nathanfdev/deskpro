<?php

namespace Orb\Jira\Entity;

use Orb\Jira\Entity;

/**
 * Issue Priority Class
 * Emulates Jira issue priority
 *
 * @author Abhinav Kumar <work@abhinavkumar.in>
 */
class Priority extends Entity
{
	/**
	 * {@inheritdoc}
	 */
	public function __construct(array $params = array())
	{
		if (!isset($params['id']) || !isset($params['name'])) {
			return false;
		}
		
		$this->_id		= $params['id'];
		$this->_name	= $params['name'];
	}
}