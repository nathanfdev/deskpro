<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage DBAL
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\DBAL\Logging;

/**
 * Allows you to hook up multiple loggers and they all get called
 * for the logger requests.
 */
class DelegateLogger implements \Doctrine\DBAL\Logging\SQLLogger
{
	/**
	 * @var \Doctrine\DBAL\Logging\SQLLogger[]
	 */
	protected $registered_loggers = array();

	public function addLogger(\Doctrine\DBAL\Logging\SQLLogger $logger, $identifier)
	{
		$this->registered_loggers[$identifier] = $logger;
	}
	
	public function getLogger($identifier)
	{
		if (isset($this->registered_loggers[$identifier])) {
			return $this->registered_loggers[$identifier];
		}
		else {
			return null;
		}
	}
	
	public function startQuery($sql, array $params = null, array $types = null)
	{
		foreach ($this->registered_loggers as $logger) {
			$logger->startQuery($sql, $params, $types);
		}
	}

	public function stopQuery()
	{
		foreach ($this->registered_loggers as $logger) {
			$logger->stopQuery();
		}
	}
}