<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Controller
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO\Controller;
use \Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The abstract controller sets up some default objects.
 */
abstract class AbstractController extends \DeskPRO\HttpKernel\Controller\Controller
{
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

	/**
	 * Input reader
	 * @var Orb\Input\Reader\Reader
	 */
	protected $in;

	/**
	 * A generic value cleaner
	 * @var Orb\Input\Cleaner\Cleaner
	 */
	protected $cleaner;

	/**
	 * Shared template vars
	 * @var ArrayObject
	 */
	protected $tplvars;

	/**
	 * Fetch settings
	 * @var DeskPRO\Settings\Settings
	 */
	protected $settings;

	/**
	 * The session
	 * @var DeskPRO\HttpFoundation\Session
	 */
	protected $session;

	/**
	 * An empty callback function
	 */
	protected function init()
	{
		$this->em       = $this['doctrine.orm.entity_manager'];
		$this->db       = $this['database_connection'];
		$this->in       = $this['deskpro.core.input_reader'];
		$this->cleaner  = $this['deskpro.core.input_cleaner'];
		$this->settings = $this['deskpro.core.settings'];
		$this->session  = $this['session'];

		$this['templating']->resetTemplateVars();
		$this->tplvars = $this['templating']->getTemplateVarsObject();
	}

	

	/**
	 * Is this a POST request?
	 *
	 * @return bool
	 */
	public function isPostRequest()
	{
		return ($this['request']->getMethod() == 'POST');
	}
}