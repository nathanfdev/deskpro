<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Controller
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Controller;

use \Symfony\Component\DependencyInjection\ContainerInterface;
use \Application\DeskPRO\App;

use \Application\DeskPRO\Build\Upgrader;
use \Application\DeskPRO\Build\VersionReader;

/**
 * The abstract controller sets up some default objects.
 */
abstract class AbstractController extends \Application\DeskPRO\HttpKernel\Controller\Controller
{
	/**
	 * Entity manager
	 * @var \Doctrine\ORM\EntityManager
	 */
	public $em;

	/**
	 * Plain database connection for raw queries
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	public $db;

	/**
	 * Input reader
	 * @var \Orb\Input\Reader\Reader
	 */
	public $in;

	/**
	 * A generic value cleaner
	 * @var \Orb\Input\Cleaner\Cleaner
	 */
	public $cleaner;

	/**
	 * Shared template vars
	 * @var ArrayObject
	 */
	public $tplvars;

	/**
	 * @var \Application\DeskPRO\Templating\Engine
	 */
	public $tpl;

	/**
	 * Fetch settings
	 * @var \Application\DeskPRO\Settings\Settings
	 */
	public $settings;

	/**
	 * The session
	 * @var \Application\DeskPRO\HttpFoundation\Session
	 */
	public $session;

	/**
	 * An empty callback function
	 */
	protected function init()
	{
		$this->em       = $this->get('doctrine.orm.entity_manager');
		$this->db       = $this->get('database_connection');
		$this->in       = $this->get('deskpro.core.input_reader');
		$this->cleaner  = $this->get('deskpro.core.input_cleaner');
		$this->settings = $this->get('deskpro.core.settings');
		$this->session  = $this->get('session');

		$this->tpl = $this->get('templating');
	}



	/**
	 * Is this a POST request?
	 *
	 * @return bool
	 */
	public function isPostRequest()
	{
		return ($this->get('request')->getMethod() == 'POST');
	}
}