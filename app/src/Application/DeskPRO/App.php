<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Application\DeskPRO;

use Application\DeskPRO\Entity;
use Application\DeskPRO\People\PersonGuest;
use Orb\Util\Arrays;

/**
 * A global singleton that facilitates fetching well known objects and values.
 *
 * @static
 * @deprecated The real container should be used
 */
class App
{
	const DEFAULT_NAME = '__default__';

	/**
	 * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	public static $container;

	/**
	 * An array of loaded config files.
	 * @var array
	 */
	protected static $_fileconfig = array();

	/**
	 * Array of instantiated API handlers
	 * @var array
	 */
	protected static $_api_handlers = null;

	/**
	 * The person who is making the request, or the person who is authorizing
	 * the request.
	 *
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected static $_current_person = null;


	/**
	 * Set the person who is making the request, or the person who is authorizing
	 * the request.
	 *
	 * @param \Application\DeskPRO\Entity\Person $person
	 */
	public static function setCurrentPerson(Entity\Person $person = null)
	{
		if (!$person) {
			$person = new PersonGuest();
		}
		self::$_current_person = $person;
	}


	/**
	 * Get the person who is making the curent request.
	 *
	 * @return \Application\DeskPRO\Entity\Person
	 */
	public static function getCurrentPerson()
	{
		return self::$_current_person;
	}


	/**
	 * Get a registered container.
	 *
	 * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	public static function getContainer()
	{
		return self::$container;
	}


	/**
	 * @param $service_name
	 * @return object
	 */
	public static function get($service_name)
	{
		return self::$container->get($service_name);
	}


	/**
	 * @param $service_name
	 * @return mixed
	 */
	public static function getSystemService($service_name)
	{
		return self::$container->getSystemService($service_name);
	}


	/**
	 * @param string $id
	 * @return \Application\DeskPRO\DependencyInjection\SystemServices\BaseRepositoryService
	 */
	public static function getDataService($id)
	{
		return self::$container->getSystemService($id . 'Data');
	}


	/**
	 * @param $service_name
	 * @param array $options
	 * @return mixed
	 */
	public static function getSystemObject($service_name, array $options = array())
	{
		return self::$container->getSystemObject($service_name, $options);
	}


	/**
	 * @param $service_name
	 * @return bool
	 */
	public static function has($service_name)
	{
		return self::$container->has($service_name);
	}


	/**
	 * @return object
	 */
	public static function getSearchAdapter()
	{
		return self::$container->get('deskpro.search_adapter');
	}


	/**
	 * @return DBAL\Connection
	 */
	public static function getDb()
	{
		return self::$container->getDb();
	}


	/**
	 * @param string $type
	 * @return DBAL\Connection
	 */
	public static function getDbRead($type = 'default', array $context = null)
	{
		return self::getContainer()->getDbRead($type, $context);
	}


	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	public static function getOrm()
	{
		return self::$container->getEm();
	}


	/**
	 * @return \Symfony\Component\HttpFoundation\Request
	 */
	public static function getRequest()
	{
		return self::$container->getRequest();
	}


	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public static function getResponse()
	{
		return self::$container->getResponse();
	}


	/**
	 * Get the session
	 *
	 * @return \Application\DeskPRO\HttpFoundation\Session
	 */
	public static function getSession()
	{
		return self::$container->getSession();
	}


	/**
	 * @return Mail\Mailer
	 */
	public static function getMailer()
	{
		return self::$container->getMailer();
	}


	/**
	 * @return Translate\Translate
	 */
	public static function getTranslator()
	{
		return self::$container->getTranslator();
	}


	/**
	 * @return Entity\Language
	 */
	public static function getLanguage()
	{
		return self::getTranslator()->getLanguage();
	}


	/**
	 * @return object
	 */
	public static function getTemplating()
	{
		return self::$container->get('templating');
	}


	/**
	 * @return Routing\Router
	 */
	public static function getRouter()
	{
		return self::$container->getRouter();
	}


	/**
	 * @return \Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher
	 */
	public static function getEventDispatcher()
	{
		return self::$container->getEventDispatcher();
	}


	/**
	 * Get the form factory
	 *
	 * @return \Symfony\Component\Form\FormFactory
	 */
	public static function getFormFactory()
	{
		return self::$container->getFormFactory();
	}


	/**
	 * Get the person activity logger
	 *
	 * @return \Application\DeskPRO\People\ActivityLogger\ActivityLogger
	 */
	public static function getPersonActivityLogger()
	{
		return self::$container->getPersonActivityLogger();
	}


	/**
	 * True if this is an http request. We should have a request and response object if so.
	 *
	 * @return bool
	 */
	public static function isWebRequest()
	{
		if (defined('DP_INTERFACE') && DP_INTERFACE == 'cli') {
			return false;
		}

		if (self::has('response') AND self::has('response')) {
			return true;
		}

		return false;
	}


	/**
	 * @param $entity
	 * @return \Doctrine\ORM\EntityRepository
	 */
	public static function getEntityRepository($entity)
	{
		return self::$container->getEm()->getRepository($entity);
	}


	/**
	 * @param $entity
	 * @param $id
	 * @return null|object
	 */
	public static function findEntity($entity, $id)
	{
		return self::getEntityRepository($entity)->find($id);
	}


	/**
	 * @param $entity
	 * @return string
	 */
	public static function getEntityClass($entity)
	{
		list ($namespace, $entity) = explode(':', $entity, 2);

		$class = "Application\\$namespace\\Entity\\$entity";

		return $class;
	}


	/**
	 * Get a secret key used for various hashing.
	 *
	 * @return string
	 */
	public static function getAppSecret()
	{
		$secret = self::getSetting('core.app_secret');
		if (!$secret) {
			$secret = 'secret';
		}

		return $secret;
	}


	/**
	 * Get the reference generator
	 *
	 * @return \Application\DeskPRO\RefGenerator\RefGeneratorInterface
	 */
	public static function getRefGenerator()
	{
		return self::getContainer()->getSystemService('RefGenerator');
	}


	/**
	 * Get the value of a setting.
	 *
	 * @param string $name The name of the setting to get
	 * @return string
	 */
	public static function getSetting($name)
	{
		$settings = self::$container->getSettingsHandler();
		return $settings->get($name);
	}

	/**
	 * @var array
	 */
	protected static $_api_handler_names = array(
		'tickets'                    => 'Application\\DeskPRO\\Tickets\\Tickets',
		'tickets.filters'            => 'Application\\DeskPRO\\Tickets\\Filters',
		'tickets.edit'               => 'Application\\DeskPRO\\Tickets\\TicketEdit',
		'tickets.search'             => 'Application\\DeskPRO\\Tickets\\TicketSearch',
		'custom_fields.chats'        => 'Application\\DeskPRO\\CustomFields\\ChatFields',
		'custom_fields.people'       => 'Application\\DeskPRO\\CustomFields\\PeopleFields',
		'custom_fields.tickets'      => 'Application\\DeskPRO\\CustomFields\\TicketFields',
		'custom_fields.articles'     => 'Application\\DeskPRO\\CustomFields\\ArticleFields',
		'custom_fields.feedback'      => 'Application\\DeskPRO\\CustomFields\\FeedbackFields',
		'custom_fields.organizations' => 'Application\\DeskPRO\\CustomFields\\OrganizationFields',
		'custom_fields.products'      => 'Application\\DeskPRO\\CustomFields\\ProductFields',
		'custom_fields.util'          => 'Application\\DeskPRO\\CustomFields\\Util',
		'filestorage'                 => '',
	);

	/**
	 * Get an API handler. It will be instantiated if it hasn't been already.
	 * This is a DeskPRO-specific way to instantiate services that doesn't use
	 * the usual DI handler used in Symfony.
	 *
	 * They will likely move to a dedicated DI later. For now, just hard-code
	 * them in here.
	 *
	 * @deprecated All of these should be services, or created as "system services"
	 * @param string $name Name of the API handler
	 * @throws \OutOfBoundsException
	 */
	public static function getApi($name)
	{
		if (isset(self::$_api_handlers[$name])) {
			return self::$_api_handlers[$name];
		}

		if (!isset(self::$_api_handler_names[$name])) {
			throw new \OutOfBoundsException("API handler does not exist");
		}

		$classname = self::$_api_handler_names[$name];
		self::$_api_handlers[$name] = new $classname();

		return self::$_api_handlers[$name];
	}


	/**
	 * Loads userconfig from the filesystem
	 * @param string $name The name of the user config
	 * @throws \UnexpectedValueException
	 */
	protected static function _loadConfig($name = null)
	{
		if ($name != self::DEFAULT_NAME) {
			$name = preg_replace('#[^a-zA-Z0-9\-_]#', '', $name);
			$filename = 'config.' . $name . '.php';
			$filepath = DP_ROOT . "/sys/config/$filename";

			require($filepath);
			if (!isset($CONFIG)) {
				throw new \UnexpectedValueException("$filename does not define \$CONFIG");
			}

			self::$_fileconfig[$name] = $CONFIG;
		} else {
			global $DP_CONFIG;
			$name = self::DEFAULT_NAME;
			self::$_fileconfig[$name] = $DP_CONFIG;
		}
	}


	/**
	 * Read a config array from a standardly named config file.
	 *
	 * @throws \RuntimeException|\UnexpectedValueException
	 * @param string $name
	 * @return array
	 */
	public static function getConfigFromFile($name)
	{
		if (!$name OR $name != self::DEFAULT_NAME) {
			$name = preg_replace('#[^a-zA-Z0-9\-_]#', '', $name);
			$filename = 'config.' . $name . '.php';
		} else {
			$filename = 'config.php';
		}

		$filepath = DP_ROOT . "/sys/config/$filename";

		if (!file_exists($filepath)) {
			throw new \RuntimeException("$filename does not exist");
		}

		require($filepath);
		if (!isset($CONFIG)) {
			throw new \UnexpectedValueException("$filename does not define \$CONFIG");
		}

		return $CONFIG;
	}


	/**
	 * Get a config value from config.
	 *
	 * If $config_name is null, then entire config array from the file will be returned.
	 * $config_name can use dot notation to denote deep array keys.
	 *
	 * @param string $config_name  The config value to get
	 * @param mixed  $default      The value to return if no such key exists
	 * @param string $file_name    The file to fetch it form
	 * @return array
	 */
	public static function getConfig($config_name, $default = null, $file_name = self::DEFAULT_NAME)
	{
		if ($config_name == 'enable_twitter' && !(isset($GLOBALS['DP_CONFIG']['enable_twitter']) && !$GLOBALS['DP_CONFIG']['enable_twitter']) && !defined('DPC_IS_CLOUD')) {
			return true;
		}

		if (!isset(self::$_fileconfig[$file_name])) {
			self::_loadConfig($file_name);
		}

		$value = Arrays::getValue(self::$_fileconfig[$file_name], $config_name);
		if ($value === null) $value = $default;

		return $value;
	}


	/**
	 * Get a new logger for some kind of thing/session
	 *
	 * @param string $log_name
	 * @param string $session_name
	 * @return \Application\DeskPRO\Log\Logger
	 */
	public static function createNewLogger($log_name, $session_name)
	{
		if ($log_name == 'error_log') {
			$logger = new \Application\DeskPRO\Log\DbErrorLogger();
		} else {
			$logger = new \Application\DeskPRO\Log\Logger();
		}
		$logger->setLogName($log_name);
		$logger->setSessionName($session_name);

		// Indent formatter by default
		$indent_filter = new \Orb\Log\Filter\IndentFilter();
		$logger->addFilter($indent_filter);

		// Writer to the DB
		if (strpos($log_name, 'worker_job') !== 0 || !defined('DP_DISABLE_DBCRONLOG')) {
			$writer = new \Application\DeskPRO\Log\Writer\LogItemEntity();
			$writer->addFilter(new \Orb\Log\Filter\PriorityFilter(\Orb\Log\Logger::INFO));
			$logger->addWriter($writer);
		}

		return $logger;
	}
}
