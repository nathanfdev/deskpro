<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category DependencyInjection
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\DependencyInjection;

use Symfony\Component\DependencyInjection\Container;

/**
 * This is an extension to the DI container that knows how to initialize
 * some services if they aren't registered and if they have a corresponding
 * factory in SystemServices.
 *
 * These services and factories use already registered services such as settings or database connections
 * to create themselves lazily.
 */
class DeskproContainer extends Container
{
	/**#@+
	 * Names of common services
	 */
	const SERVICE_DB                 = 'database_connection';
	const SERVICE_ORM                = 'doctrine.orm.entity_manager';
	const SERVICE_EM                 = 'doctrine.orm.entity_manager';
	const SERVICE_INPUT_READER       = 'deskpro.core.input_reader';
	const SERVICE_INPUT_CLEANER      = 'deskpro.core.input_cleaner';
	const SERVICE_SETTINGS           = 'deskpro.core.settings';
	const SERVICE_SESSION            = 'session';
	const SERVICE_ROUTER             = 'router';
	const SERVICE_REQUEST            = 'request';
	const SERVICE_RESPONSE           = 'response';
	const SERVICE_MAILER             = 'mailer';
	const SERVICE_TRANSLATOR         = 'deskpro.core.translate';
	const SERVICE_EVENT_DISPATCHER   = 'event_dispatcher';
	const SERVICE_FORM_FACTORY       = 'form.factory';
	const SERVICE_SEARCH_ENGINE      = 'deskpro.search_engine';
	const SERVICE_TEMPLATING         = 'templating';
	const SERVICE_SEARCH             = 'deskpro.search_adapter';
	const SERVICE_PERSON_ACTIVITY_LOGGER = 'deskpro.person_activity_logger';
	/**#@-*/

	protected $system_services = array();

	/**
	 * This returns a reference to a system service.
	 *
	 * @throws \InvalidArgumentException
	 * @param string $id
	 * @return mixed
	 */
	public function getSystemService($id)
	{
		if (isset($this->system_services[$id])) {
			$this->system_services[$id] = $id;
		}

		$classname = 'Application\\DeskPRO\\DependencyInjection\\SystemServices\\' . $this->camelize($id) . 'Service';

		if (!class_exists($classname)) {
			throw new \InvalidArgumentException("Invalid service `$id`, tried class `$classname`");
		}

		$obj = $classname::create($this);

		$this->system_services[$id] = $obj;

		return $obj;
	}


	/**
	 * This calls a system factory and returns a new instance of some kind of object.
	 *
	 * @throws \InvalidArgumentException
	 * @param string $id
	 * @return mixed
	 */
	public function getSystemObject($id, array $options = array())
	{
		$classname = 'Application\\DeskPRO\\DependencyInjection\\SystemServices\\' . $this->camelize($id) . 'Factory';

		if (!class_exists($classname)) {
			throw new \InvalidArgumentException("Invalid factory `$id`");
		}

		$options = new \Orb\Util\CheckedOptionsArray($options);
		$obj = $classname::create($this, $options);
		return $obj;
	}


	/**
	 * Get the autoloader
	 *
	 * @var \Orb\Util\ClassLoader
	 */
	public function getClassLoader()
	{
		if (isset($GLOBALS['DP_AUTOLOADER'])) {
			return $GLOBALS['DP_AUTOLOADER'];
		}

		return null;
	}


	/**
	 * @return \Orb\Input\Reader\Reader
	 */
	public function getIn()
	{
		return $this->get(self::SERVICE_INPUT_READER);
	}


	/**
	 * @return \Orb\Input\Cleaner\Cleaner
	 */
	public function getInputCleaner()
	{
		return $this->get(self::SERVICE_INPUT_CLEANER);
	}


	/**
	 * Get the search adapter.
	 *
	 * @return \Application\DeskPRO\Search\Adapter\AbstractAdapter
	 */
	public function getSearchAdapter()
	{
		return $this->get(self::SERVICE_SEARCH);
	}


	/**
	 * Get the DB abstraction object.
	 *
	 * @return \Application\DeskPRO\DBAL\Connection
	 */
	public function getDb()
	{
		return $this->get(self::SERVICE_DB, self::DEFAULT_NAME);
	}


	/**
	 * @deprecated Use getEm instead.
	 */
	public function getOrm()
	{
		return $this->get(self::SERVICE_ORM);
	}



	/**
	 * Get the entity manager.
	 *
	 * @return \Doctrine\ORM\EntityManager
	 */
	public function getEm()
	{
		return $this->get(self::SERVICE_EM);
	}



	/**
	 * Get the request
	 *
	 * @return \Symfony\Component\HttpFoundation\Request
	 */
	public function getRequest()
	{
		return $this->get(self::SERVICE_REQUEST);
	}


	/**
	 * Get the response
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function getResponse()
	{
		return $this->get(self::SERVICE_RESPONSE);
	}


	/**
	 * Get the session
	 *
	 * @return \Application\DeskPRO\HttpFoundation\Session
	 */
	public function getSession()
	{
		return $this->get(self::SERVICE_SESSION);
	}


	/**
	 * Get the mailer
	 *
	 * @return \Application\DeskPRO\Mail\Mailer
	 */
	public function getMailer()
	{
		return $this->get(self::SERVICE_MAILER);
	}


	/**
	 * Get the translator
	 *
	 * @return \Application\DeskPRO\Translate\Translate
	 */
	public function getTranslator()
	{
		return $this->get(self::SERVICE_TRANSLATOR);
	}


	/**
	 * Get the templating service
	 *
	 * @return \Symfony\Component\Templating\EngineInterface
	 */
	public function getTemplating()
	{
		return $this->get(self::SERVICE_TEMPLATING);
	}


	/**
	 * Get the router
	 *
	 * @return \Symfony\Component\Routing\Router
	 */
	public function getRouter()
	{
		return $this->get(self::SERVICE_ROUTER);
	}


	/**
	 * Get the app event dispatcher
	 *
	 * @return \Symfony\Bundle\FrameworkBundle\ContainerAwareEventDispatcher
	 */
	public function getEventDispatcher()
	{
		return $this->get(self::SERVICE_EVENT_DISPATCHER);
	}


	/**
	 * Get the form factory
	 *
	 * @return \Symfony\Component\Form\FormFactory
	 */
	public function getFormFactory()
	{
		return $this->get(self::SERVICE_FORM_FACTORY);
	}


	/**
	 * Get the searcher
	 *
	 * @return \Application\DeskPRO\Search\Adapter\AbstractAdapter
	 */
	public function getSearchEngine()
	{
		return $this->get(self::SERVICE_SEARCH_ENGINE);
	}


	/**
	 * @return \Imagine\Image\ImagineInterface
	 */
	public function getImagine()
	{
		return $this->getSystemService('imagine');
	}


	/**
	 * Get the person activity logger
	 *
	 * @return \Application\DeskPRO\People\ActivityLogger\ActivityLogger
	 */
	public function getPersonActivityLogger()
	{
		return $this->get(self::SERVICE_PERSON_ACTIVITY_LOGGER);
	}


	/**
	 * Get the reference generator
	 *
	 * @return \Application\DeskPRO\RefGenerator\RefGeneratorInterface
	 */
	public function getRefGenerator()
	{
		return $this->get('deskpro.ref_generator');
	}


	/**
	 * Get the queuer
	 *
	 * @return \Application\DeskPRO\Queue\Queue
	 */
	public function getQueue($name)
	{
		$adapter = new \Application\DeskPRO\Queue\Adapter\QueueItemEntity(array('em' => $this->getOrm(), 'name' => $name));
		$queue = new \Application\DeskPRO\Queue\Queue($adapter);

		return $queue;
	}


	/**
	 * Get a cache object, or null if no cache exists.
	 *
	 * @param string $name                Name of the cache
	 * @param bool   $deafult_blackhole   If the cache doesnt exist, default to a black hole. If this is false, null is returned on no cache
	 * @return \Zend\Cache\Frontend\Core
	 */
	public function getCache($name, $default_blackhole = true)
	{
		$service_name = 'deskpro.cache.' . $name;

		if ($name && self::has($service_name) AND $this->get($service_name)) {
			return $this->get($service_name);
		}

		if ($default_blackhole) {
			if (self::has('deskpro.cache.blackhole')) {
				return $this->get('deskpro.cache.blackhole');
			}

			$blackhole = \Zend\Cache\Cache::factory('Core', 'BlackHole', array(
				'caching' => false,
				'lifetime' => null,
				'logging' => false,
			), array());

			$this->getContainer()->set('deskpro.cache.blackhole', $blackhole);

			return $blackhole;
		}

		return null;
	}


	/**
	 * Get the value of a setting.
	 *
	 * @param string $name The name of the setting to get
	 * @return string
	 */
	public function getSetting($name)
	{
		$settings = $this->get(self::SERVICE_SETTINGS);
		return $settings->get($name);
	}
}
