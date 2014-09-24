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
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection;

use Orb\Util\Util;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

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
	/**
	 * @var \DeskPRO\Kernel\AbstractKernel
	 */
	public $kernel;

	/**
	 * @var array
	 */
	protected $system_services = array();

	/**
	 * @var array
	 */
	protected $db_read_conns = array();


	/**
	 * @return \DeskPRO\Kernel\AbstractKernel
	 */
	public function getKernel()
	{
		return $this->kernel;
	}


	public function __construct(ParameterBagInterface $parameterBag = null)
	{
		require_once DP_ROOT . '/sys/load_config.php';

		$GLOBALS['DP_CONTAINER'] = $this;
		parent::__construct($parameterBag);
	}


	/**
	 * @return bool
	 */
	public function isDebug()
	{
		return $this->kernel ? $this->kernel->getEnvironment() == 'dev' : true;
	}


	/**
	 * @return string
	 */
	public function getEnvironment()
	{
		return $this->kernel ? $this->kernel->getEnvironment() : 'dev';
	}


	/**
	 * Checks if a service has been initialized.
	 *
	 * has() checks if a service has been initialized OR if it has a definition to create it.
	 * This just checks if a service has been initialized. You use this when you want to see
	 * if a certain service has been created already, and you'd use has() to see if a service can be used.
	 *
	 * @param $id
	 * @return bool
	 */
	public function isServiceInitialized($id)
	{
		return isset($this->services[$id]);
	}


	/**
	 * This returns a reference to a system service.
	 *
	 * @throws \InvalidArgumentException
	 * @param string $id
	 * @return mixed
	 */
	public function getSystemService($id)
	{
		if ($this->has("deskpro.$id")) {
			return $this->get("deskpro.$id");
		}
		if (isset($this->system_services[$id])) {
			return $this->system_services[$id];
		}

		$classname = 'Application\\DeskPRO\\DependencyInjection\\SystemServices\\' . $this->camelize($id) . 'Service';
		$options = null;

		if (!class_exists($classname)) {
			if ($ent = \Orb\Util\Strings::extractRegexMatch('#^(.*?)Data$#', $id, 1)) {
				$classname = 'Application\\DeskPRO\\DependencyInjection\\SystemServices\\BaseRepositoryService';
				$options = array('entity' => 'DeskPRO:' . ucfirst($ent));
			} else {
				throw new \InvalidArgumentException("Invalid service `$id`, tried class `$classname`");
			}
		}

		if ($options) {
			$obj = $classname::create($this, $options);
		} else {
			$obj = $classname::create($this);
		}

		$this->system_services[$id] = $obj;

		return $obj;
	}


	/**
	 * Unsets a system service so next time it's requested, it will be re-created.
	 *
	 * @param string $id
	 */
	public function resetSystemService($id)
	{
		if (isset($this->system_services[$id])) {
			unset($this->system_services[$id]);
		}
	}


	/**
	 * @param string $id
	 * @return \Application\DeskPRO\DependencyInjection\SystemServices\BaseRepositoryService
	 */
	public function getDataService($id)
	{
		return $this->getSystemService($id . 'Data');
	}


	/**
	 * This calls a system factory and returns a new instance of some kind of object.
	 *
	 * @throws \InvalidArgumentException
	 * @param string $id
	 * @param array $options
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
	 * @return \Application\DeskPRO\Input\Reader
	 */
	public function getIn()
	{
		return $this->get('deskpro.core.input_reader');
	}


	/**
	 * @return \Orb\Input\Cleaner\Cleaner
	 */
	public function getInputCleaner()
	{
		return $this->get('deskpro.core.input_cleaner');
	}


	/**
	 * Get the search adapter.
	 *
	 * @return \Application\DeskPRO\Search\Adapter\AbstractAdapter
	 */
	public function getSearchAdapter()
	{
		return $this->get('deskpro.search_adapter');
	}


	/**
	 * Get the DB abstraction object.
	 *
	 * @return \Application\DeskPRO\DBAL\Connection
	 */
	public function getDb()
	{
		return $this->get('database_connection');
	}


	/**
	 * Gets a DB reader.
	 *
	 * There can be many types of readers:
	 * - reports
	 * - search
	 * - search.tickets
	 * etc
	 *
	 * In config.php you can define connection params for each type.
	 * db_read is the main fallback connection for all readers.
	 *
	 * You can get more specific by naming:
	 * - getDbRead('reports') gets params from config db_read_reports
	 * - getDbRead('search') gets params from config db_read_search
	 * - getDbRead('search.tickets') gets params from config db_read_search_tickets,
	 * and falls back on db_read_search if it doesnt exist.
	 *
	 * If the config value is an array of arrays, then it's expected that there are multiple
	 * databases to choose from and one is selected at random.
	 *
	 * @param string $type
	 * @param array $context = null
	 * @return \Application\DeskPRO\DBAL\Connection
	 */
	public function getDbRead($type = 'default', array $context = null)
	{
		$type_key_fn = dp_get_config('db_read_mapper');
		if ($type_key_fn) {
			$new_type = call_user_func($type_key_fn, $type, $context);
			if ($new_type) {
				$type = $new_type;
			}
		}

		// Already initialised
		if (isset($this->db_read_conns[$type])) {
			return $this->db_read_conns[$type];
		}

		// Get an appropriate connection
		$parts = explode('.', $type);
		do {
			$config_key = 'db_read_' . implode('_', $parts);
			$config_key = rtrim($config_key, '_');

			$type_key = implode('.', $parts);
			if (!$type_key) {
				$type_key = 'default';
			}

			if (isset($this->db_read_conns[$type_key])) {
				// Assign to the speciifc type so next time we can return earlier
				$this->db_read_conns[$type] = $this->db_read_conns[$type_key];
				return $this->db_read_conns[$type];
			}

			// Init the connection
			$read_configs = dp_get_config($config_key);
			if ($read_configs) {
				$read = null;

				// Single config
				if (isset($read_configs['host']) || isset($read_configs['dbname'])) {
					$read = $read_configs;

				// Multiple config, choose one at random
				} else {
					$read = $read_configs[array_rand($read_configs)];
				}

				if ($read && !empty($read['host']) && !empty($read['dbname'])) {
					$db = $this->get('doctrine.dbal.connection_factory')->createConnection(array(
						'driver'        => 'pdo_mysql',
						'host'          => $read['host'],
						'user'          => $read['user'],
						'password'      => $read['password'],
						'dbname'        => $read['dbname']
					));
					$this->db_read_conns[$type] = $db;
					return $db;
				}
			}
		} while (array_pop($parts));

		// No read config, return default connection
		$this->db_read_conns[$type] = $this->getDb();
		return $this->db_read_conns[$type];
	}


	/**
	 * @deprecated Use getEm instead.
	 */
	public function getOrm()
	{
		return $this->get('doctrine.orm.entity_manager');
	}



	/**
	 * Get the entity manager.
	 *
	 * @return \Doctrine\ORM\EntityManager
	 */
	public function getEm()
	{
		return $this->get('doctrine.orm.entity_manager');
	}



	/**
	 * Get the request
	 *
	 * @deprecated Should inject the request into the current controller action
	 * @return \Symfony\Component\HttpFoundation\Request
	 */
	public function getRequest()
	{
		return $this->get('request');
	}


	/**
	 * Get the response
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function getResponse()
	{
		return $this->get('response');
	}


	/**
	 * Get the session
	 *
	 * @return \Application\DeskPRO\HttpFoundation\Session
	 */
	public function getSession()
	{
		return $this->get('session');
	}


	/**
	 * Get the mailer
	 *
	 * @return \Application\DeskPRO\Mail\Mailer
	 */
	public function getMailer()
	{
		return $this->get('mailer');
	}


	/**
	 * Get the translator
	 *
	 * @return \Application\DeskPRO\Translate\Translate
	 */
	public function getTranslator()
	{
		return $this->get('deskpro.core.translate');
	}


	/**
	 * Get the templating service
	 *
	 * @return \Application\DeskPRO\Templating\Engine
	 */
	public function getTemplating()
	{
		return $this->get('templating');
	}


	/**
	 * Get the twig service
	 *
	 * @return \Application\DeskPRO\Twig\Environment
	 */
	public function getTwig()
	{
		return $this->get('twig');
	}


	/**
	 * Get the router
	 *
	 * @return \Application\DeskPRO\Routing\Router
	 */
	public function getRouter()
	{
		return $this->get('router');
	}


	/**
	 * @return \Symfony\Component\Validator\Validator
	 */
	public function getValidator()
	{
		return $this->get('validator');
	}


	/**
	 * Get the app event dispatcher
	 *
	 * @return \Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher
	 */
	public function getEventDispatcher()
	{
		return $this->get('event_dispatcher');
	}


	/**
	 * Get the form factory
	 *
	 * @return \Symfony\Component\Form\FormFactory
	 */
	public function getFormFactory()
	{
		return $this->get('form.factory');
	}


	/**
	 * Get the searcher
	 *
	 * @return \Application\DeskPRO\NewSearch\SearchEngine\SearchEngine
	 */
	public function getSearchEngine()
	{
		return $this->getSystemService('SearchEngine');
	}


	/**
	 * Get the context factory
	 *
	 * @return \Application\DeskPRO\NewSearch\SearchEngine\SearchContextFactory
	 */
	public function getSearchContextFactory()
	{
		return $this->getSystemService('SearchContextFactory');
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
		return $this->get('deskpro.person_activity_logger');
	}


	/**
	 * Get the reference generator
	 *
	 * @return \Application\DeskPRO\RefGenerator\RefGeneratorInterface
	 */
	public function getRefGenerator()
	{
		return $this->getSystemService('RefGenerator');
	}


	/**
	 * @return \Application\DeskPRO\Email\EmailAccount\EmailAccountManager
	 */
	public function getEmailAccountManager()
	{
		return $this->getSystemService('email_account_manager');
	}


	/**
	 * Get the queuer
	 *
	 * @param string $name
	 * @return \Application\DeskPRO\Queue\Queue
	 */
	public function getQueue($name)
	{
		$adapter = new \Application\DeskPRO\Queue\Adapter\QueueItemEntity(array('em' => $this->getEm(), 'name' => $name));
		$queue = new \Application\DeskPRO\Queue\Queue($adapter, array('name' => $name));

		return $queue;
	}


	/**
	 * @return \Application\DeskPRO\Attachments\AcceptAttachment
	 */
	public function getAttachmentAccepter()
	{
		return $this->getSystemService('attachment_accepter');
	}


	/**
	 * @return \Application\DeskPRO\BlobStorage\DeskproBlobStorage
	 */
	public function getBlobStorage()
	{
		return $this->getSystemService('blob_storage');
	}


	/**
	 * @return \Application\DeskPRO\AgentAlert\AlertSender
	 */
	public function getAgentAlertSender()
	{
		return $this->getSystemService('agent_alert_sender');
	}


	/**
	 * @return \Application\DeskPRO\Tickets\TicketManager
	 */
	public function getTicketManager()
	{
		return $this->getSystemService('ticket_manager');
	}


	/**
	 * @return \Application\DeskPRO\TicketLayout\TicketLayoutManager
	 */
	public function getTicketLayoutManager()
	{
		return $this->getSystemService('ticket_layout_manager');
	}


	/**
	 * @return \Application\DeskPRO\CustomFields\TicketFieldManager
	 */
	public function getTicketFieldManager()
	{
		return $this->getSystemService('ticket_fields_manager');
	}


	/**
	 * @return \Application\DeskPRO\Departments\TicketDepartments
	 */
	public function getTicketDepartments()
	{
		return $this->getSystemService('ticket_departments');
	}


	/**
	 * @return \Application\DeskPRO\Departments\ChatDepartments
	 */
	public function getChatDepartments()
	{
		return $this->getSystemService('chat_departments');
	}


	/**
	 * @return \Application\DeskPRO\Tickets\TicketCategories
	 */
	public function getTicketCategories()
	{
		return $this->getSystemService('ticket_categories');
	}


	/**
	 * @return \Application\DeskPRO\Tickets\TicketPriorities
	 */
	public function getTicketPriorities()
	{
		return $this->getSystemService('ticket_priorities');
	}


	/**
	 * @return \Application\DeskPRO\Tickets\TicketWorkflows
	 */
	public function getTicketWorkflows()
	{
		return $this->getSystemService('ticket_workflows');
	}


	/**
	 * @return \Application\DeskPRO\Tickets\Filters\FilterChangeDetector
	 */
	public function getTicketFilterChangeDetector()
	{
		return $this->getSystemService('ticket_filter_change_detector');
	}


	/**
	 * @return \Application\DeskPRO\Products\Products
	 */
	public function getProducts()
	{
		return $this->getSystemService('products');
	}


	/**
	 * @return \Application\DeskPRO\People\AgentGroups
	 */
	public function getAgentGroups()
	{
		return $this->get('deskpro.people.agent_groups');
	}


	/**
	 * @return \Application\DeskPRO\People\UserGroups
	 */
	public function getUserGroups()
	{
		return $this->get('deskpro.people.user_groups');
	}


	/**
	 * @return \Application\DeskPRO\CustomFields\PersonFieldManager
	 */
	public function getPersonFieldManager()
	{
		return $this->getSystemService('person_fields_manager');
	}


	/**
	 * @return \Application\DeskPRO\CustomFields\FieldManager
	 */
	public function getOrgFieldManager()
	{
		return $this->getSystemService('org_fields_manager');
	}


	/**
	 * Get the value of a setting.
	 *
	 * @param string $name The name of the setting to get
	 * @param mixed $default
	 * @return string
	 */
	public function getSetting($name, $default = null)
	{
		$settings = $this->get('deskpro.core.settings');
		return $settings->get($name, $default);
	}


	/**
	 * Get the settings object
	 *
	 * @return \Application\DeskPRO\Settings\Settings
	 */
	public function getSettingsHandler()
	{
		$settings = $this->get('deskpro.core.settings');
		return $settings;
	}


	/**
	 * @return \Application\DeskPRO\Monolog\LoggerManager
	 */
	public function getLoggerManager()
	{
		return $this->getSystemService('logger_manager');
	}


	/**
	 * Get a value from the main system configuration
	 *
	 * @param string $name
	 * @param mixed  $default
	 * @return mixed
	 */
	public function getSysConfig($name, $default = null)
	{
		if ($name == '*') {
			return $GLOBALS['DP_CONFIG'];
		}

		$value = dp_get_config($name, $default);

		return $value;
	}


	/**
	 * @return \Application\DeskPRO\DependencyInjection\SystemServices\AgentDataService
	 */
	public function getAgentData()
	{
		return $this->getDataService('Agent');
	}


	/**
	 * @return \Application\DeskPRO\DependencyInjection\SystemServices\LanguageDataService
	 */
	public function getLanguageData()
	{
		return $this->getDataService('Language');
	}


	/**
	 * @return \Application\DeskPRO\Translate\ObjectLangRepository
	 */
	public function getObjectLangRepository()
	{
		return $this->getSystemService('object_lang_repository');
	}


	/**
	 * @return \Orb\GeoIp\AbstractGeoIp
	 */
	public function getGeoIp()
	{
		return $this->getSystemService('geo_ip');
	}


	/**
	 * Get the path to PHP executable used on the CLI.
	 *
	 * Returns false if the path could not be found and if 'php_path' in config is not set.
	 *
	 * @return string
	 */
	public function getPhpBinaryPath()
	{
		return dp_get_php_path();
	}


	/**
	 * Get the path to mysqldump executable used on the CLI.
	 *
	 * Returns false if the path could not be found and if 'mysqldump_path' in config is not set.
	 *
	 * @return string
	 */
	public function getMysqldumpBinaryPath()
	{
		return dp_get_mysqldump_path();
	}


	/**
	 * Gets the path to the 'mysql' binary.
	 *
	 * * Returns false if the path could not be found and if 'mysql_path' in config is not set.
	 *
	 * @return string
	 */
	public function getMysqlBinaryPath()
	{
		return dp_get_mysql_path();
	}


	/**
	 * @return string
	 */
	public function getLogDir()
	{
		return dp_get_log_dir();
	}


	/**
	 * @return string
	 */
	public function getBlobDir()
	{
		return dp_get_blob_dir();
	}


	/**
	 * @return string
	 */
	public function getBackupDir()
	{
		return dp_get_backup_dir();
	}


	/**
	 * Checks a static security token
	 *
	 * @param string $name
	 * @param string $token
	 * @return bool
	 */
	public function checkStaticSecurityToken($name, $token)
	{
		return Util::checkStaticSecurityToken($token, md5($this->getSetting('core.app_secret', 'secret') . $name));
	}


	/**
	 * Generate static security token
	 *
	 * @param string $name
	 * @param int $timeout
	 * @return string
	 */
	public function generateStaticSecurityToken($name, $timeout = 18000)
	{
		return Util::generateStaticSecurityToken(md5($this->getSetting('core.app_secret', 'secret') . $name), $timeout);
	}


	/**
	 * @return \Application\DeskPRO\App\AppManager
	 */
	public function getAppManager()
	{
		return $this->getSystemService('app_manager');
	}


	/**
	 * @return \Application\DeskPRO\Tickets\Actions\ActionDef\TicketActionDefManager
	 */
	public function getTicketActionDefManager()
	{
		return $this->getSystemService('ticket_action_def_manager');
	}
}
