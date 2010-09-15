<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO\RemoteResourceListener;

use \Application\CoreBundle\Entity\RemoteResource;
use \Application\CoreBundle\Entity\RemoteRecord;

/**
 * A listener is notified when any remote record is updated.
 *
 * @see Application\CoreBundle\Entity\RemoteResource::notifyListeners
 */
abstract class AbstractListener
{
	/**
	 * Array of options
	 * @var array
	 */
	protected $_options;
	
	/**
	 * Entity manager
	 * @var DeskPRO\ORM\EntityManager
	 */
	protected $em;

	/**
	 * Plain database connection for raw queries
	 * @var DeskPRO\DBAL\Connection
	 */
	protected $db;


	public function __construct(array $options = array())
	{
		$this->_options = $options;

		if (isset($options['em'])) $this->em = $options['em'];
		if (isset($options['db'])) $this->db = $options['db'];
	}



	/**
	 * Get the value of an option
	 *
	 * @param string $key The option to get
	 * @param mixed $default What to return if the option doesnt exist
	 */
	public function getOption($key, $default = null)
	{
		return isset($this->_options[$key]) ? $this->_options[$key] : $default;
	}



	/**
	 * Check to see if an option exists
	 *
	 * @param string $key
	 * @return bool
	 */
	public function hasOption($key)
	{
		return isset($this->_options[$key]);
	}


	abstract public function remoteRecordUpdated(RemoteResource $resource, RemoteRecord $record);
}