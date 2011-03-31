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
	protected $em;

	/**
	 * Plain database connection for raw queries
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	protected $db;

	/**
	 * Input reader
	 * @var \Orb\Input\Reader\Reader
	 */
	protected $in;

	/**
	 * A generic value cleaner
	 * @var \Orb\Input\Cleaner\Cleaner
	 */
	protected $cleaner;

	/**
	 * Shared template vars
	 * @var ArrayObject
	 */
	protected $tplvars;

	/**
	 * @var \Application\DeskPRO\Templating\Engine
	 */
	protected $tpl;

	/**
	 * Fetch settings
	 * @var \Application\DeskPRO\Settings\Settings
	 */
	protected $settings;

	/**
	 * The session
	 * @var \Application\DeskPRO\HttpFoundation\Session
	 */
	protected $session;

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
		$this->tpl->resetTemplateVars();
		$this->tplvars = $this->tpl->getTemplateVarsObject();

		// Automatically redirect to redirect if we need to
		// - Must be in debug
		// - In an interface script
		// - Not an AJAX request (they happen so frequently, lets not bog them down with file check each time)
		if (App::isDebug() && preg_match('#^Application\\\\(Admin|Agent|User)Bundle\\\\#', get_class($this)) && !$this->request->isXmlHttpRequest()) {

			$upgrader = new Upgrader();
			$version = VersionReader::getVersionId(VersionReader::getCurrentVersion());
			$source_version = VersionReader::getVersionId($upgrader->getNewestVersion());

			if ($version != $source_version) {
				$this->event_dispatcher->addListener('DeskPRO_onControllerPreAction', function ($event) {
					$controller = $event->info['controller'];
					$event->setResponse($controller->redirectRoute('dev_build'));
				}, -5);
			}
		}
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