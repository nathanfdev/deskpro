<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection;

use Application\DeskPRO\App;
use Application\DeskPRO\App\AgentAppPermissions;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DpSys\LowError\SystemErrorHandler;
use Orb\Util\Util;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\RouterInterface;

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
     * @var \DpSys\Kernel\AbstractKernel
     */
    public $kernel;

    /**
     * @var array
     */
    protected $system_services = [];

    /**
     * @var array
     */
    protected $db_read_conns = [];

    /**
     * @var AgentAppPermissions
     */
    protected $agent_app_perms;

    /**
     * @return \DpSys\Kernel\AbstractKernel
     */
    public function getKernel()
    {
        return $this->kernel;
    }

    public function __construct(ParameterBagInterface $parameterBag = null)
    {
        require_once DP_ROOT.'/sys/load_config.php';

        $GLOBALS['DP_CONTAINER'] = $this;
        parent::__construct($parameterBag);
    }

    /**
     * @return bool
     */
    public function isDebug()
    {
        //return $this->kernel ? $this->kernel->getEnvironment() == 'dev' : true;
        // TODO: after we remove agent interface, use the commented line instead
        return $this->kernel ? in_array($this->kernel->getEnvironment(), ['dev_old_agent', 'dev']) : true;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->kernel ? $this->kernel->getEnvironment() : 'dev';
    }

    /**
     * This returns a reference to a system service.
     *
     *
     * @param string $id
     *
     * @throws \InvalidArgumentException
     *
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

        $classname = 'Application\\DeskPRO\\DependencyInjection\\SystemServices\\'.$this->camelize($id).'Service';
        $options   = null;

        if (!class_exists($classname)) {
            if ($ent = \Orb\Util\Strings::extractRegexMatch('#^(.*?)Data$#', $id, 1)) {
                $classname = 'Application\\DeskPRO\\DependencyInjection\\SystemServices\\BaseRepositoryService';
                $options   = ['entity' => 'DeskPRO:'.ucfirst($ent)];
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
     *
     * @return \Application\DeskPRO\DependencyInjection\SystemServices\BaseRepositoryService
     */
    public function getDataService($id)
    {
        return $this->getSystemService($id.'Data');
    }

    /**
     * This calls a system factory and returns a new instance of some kind of object.
     *
     *
     * @param string $id
     * @param array  $options
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    public function getSystemObject($id, array $options = [])
    {
        $classname = 'Application\\DeskPRO\\DependencyInjection\\SystemServices\\'.$this->camelize($id).'Factory';

        if (!class_exists($classname)) {
            throw new \InvalidArgumentException("Invalid factory `$id`");
        }

        $options = new \Orb\Util\CheckedOptionsArray($options);
        $obj     = $classname::create($this, $options);

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
     * Get the DB abstraction object.
     *
     * @return \Application\DeskPRO\JobQueue\JobQueue
     */
    public function getJobQueue()
    {
        return $this->getSystemService('job_queue');
    }

    /**
     * Get the DB abstraction object.
     *
     * @return \Application\DeskPRO\JobQueue\JobSupervisor
     */
    public function getJobSupervisor()
    {
        return $this->getSystemService('job_supervisor');
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
     * @param array  $context = null
     *
     * @return \Application\DeskPRO\DBAL\Connection
     */
    public function getDbRead($type = 'default', array $context = null)
    {
        // Already initialised
        if (isset($this->db_read_conns[$type])) {
            return $this->db_read_conns[$type];
        }

        // Get an appropriate connection
        $parts = explode('.', $type);
        do {
            $config_key = 'database_advanced.read_'.implode('_', $parts);
            $config_key = rtrim($config_key, '_');

            if (isset($this->db_read_conns[$config_key])) {
                // Assign to the speciifc type so next time we can return earlier
                $this->db_read_conns[$type] = $this->db_read_conns[$config_key];

                return $this->db_read_conns[$type];
            }

            // Init the connection
            $read_configs = $this->get('deskpro.app_env')->getConfig($config_key);
            if ($read_configs) {
                $read = null;

                // Single config, cast to array
                if (isset($read_configs['host']) || isset($read_configs['dbname'])) {
                    $read_configs = [$read_configs];
                }

                shuffle($read_configs);

                $has_multiple = count($read_configs) > 1;

                // try each read until we have one that works, or they all fail
                while ($read = array_pop($read_configs)) {
                    if ($read && !empty($read['host']) && !empty($read['dbname'])) {
                        try {
                            $params = \DpRun\LowUtil::getMysqlInfoFromConfigArray($read);

                            $doctrine_params                 = $params['doctrine'];
                            $doctrine_params['wrapperClass'] = 'Application\\DeskPRO\\DBAL\\Connection';

                            // We only want to do the normal retry attempt if there's only one
                            // reader, because otherwise if there are multiple,
                            // it'll be faster/more successful to just try the next
                            $doctrine_params['dp_connect_attempts'] = $has_multiple ? 1 : 2;

                            $db = $this->get('doctrine.dbal.connection_factory')->createConnection($doctrine_params);

                            $db->connect();

                            $this->db_read_conns[$type]       = $db;
                            $this->db_read_conns[$config_key] = $db;

                            return $db;
                        } catch (\Exception $e) {
                            // Error connecting, log but ignore and try another
                            $ex = new \RuntimeException("Failed to connect to read database: {$read['user']}@{$read['host']}/{$read['dbname']}", 0, $e);
                            SystemErrorHandler::logException($ex);
                        }
                    }
                }
            }
        } while (array_pop($parts));

        // No read config, return default connection
        $this->db_read_conns[$type]       = $this->getDb();
        $this->db_read_conns[$config_key] = $this->getDb();

        return $this->db_read_conns[$type];
    }

    /**
     * @deprecated Use getEm instead
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
     * Get the DeskPRO serializer.
     *
     * @return \Application\DeskPRO\Serializer\SerializerRegistry
     */
    public function getSerializer()
    {
        return $this->getSystemService('serializer');
    }

    /**
     * Get the request.
     *
     * @deprecated Should inject the request into the current controller action
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->get('request');
    }

    /**
     * Get the response.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getResponse()
    {
        return $this->get('response');
    }

    /**
     * Get the session.
     *
     * @return \Application\DeskPRO\HttpFoundation\Session
     */
    public function getSession()
    {
        return $this->get('session');
    }

    /**
     * Get the mailer.
     *
     * @return \Application\EmailBundle\SwiftMailer\Mailer
     */
    public function getMailer()
    {
        return $this->get('mailer');
    }

    /**
     * Get the translator.
     *
     * @return \Application\DeskPRO\Translate\Translate
     */
    public function getTranslator()
    {
        return $this->get('deskpro.core.translate');
    }

    /**
     * Get the templating service.
     *
     * @return \Application\DeskPRO\Templating\Engine
     */
    public function getTemplating()
    {
        return $this->get('templating');
    }

    /**
     * Get the twig service.
     *
     * @return \Application\DeskPRO\Twig\Environment
     */
    public function getTwig()
    {
        return $this->get('twig');
    }

    /**
     * Get the router.
     *
     * @return RouterInterface
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
     * Get the app event dispatcher.
     *
     * @return \Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->get('event_dispatcher');
    }

    /**
     * Get the form factory.
     *
     * @return \Symfony\Component\Form\FormFactory
     */
    public function getFormFactory()
    {
        return $this->get('form.factory');
    }

    /**
     * Get the searcher.
     *
     * @return \Application\DeskPRO\NewSearch\SearchEngine\SearchEngine
     */
    public function getSearchEngine()
    {
        return $this->getSystemService('SearchEngine');
    }

    /**
     * Get the context factory.
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

    public function getUsersourceLogger()
    {
        static $logger = null;

        if ($logger === null) {
            $logger = new \Orb\Log\Logger();
            $logger->addWriter(new \Orb\Log\Writer\Stream($this->getLogDir().'/usersource_log.log'));
        }

        return $logger;
    }

    /**
     * Get the person activity logger.
     *
     * @return \Application\DeskPRO\People\ActivityLogger\ActivityLogger
     */
    public function getPersonActivityLogger()
    {
        return $this->get('deskpro.person_activity_logger');
    }

    /**
     * Get the reference generator.
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
        return $this->get('email.email_account_manager');
    }

    /**
     * @return \Application\DeskPRO\EmailGateway\Reader\EzcReaderFactory
     */
    public function getEmailEzcReaderFactory()
    {
        return $this->get('email.ezc_reader_factory');
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
        return $this->get('deskpro.blob_storage');
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
     * @return \Application\DeskPRO\CustomFields\BillingFieldManager
     */
    public function getBillingFieldManager()
    {
        return $this->getSystemService('billing_fields_manager');
    }

    /**
     * @return \Application\DeskPRO\CustomFields\EntityFieldManager
     */
    public function getEntityFieldManager()
    {
        return $this->getSystemService('entity_fields_manager');
    }

    /**
     * @return \Application\DeskPRO\CustomFields\OrganizationFieldManager
     */
    public function getOrgFieldManager()
    {
        return $this->getSystemService('org_fields_manager');
    }

    /**
     * @return \Application\DeskPRO\CustomFields\ChatFieldManager
     */
    public function getChatFieldManager()
    {
        return $this->getSystemService('chat_fields_manager');
    }

    /**
     * Get the value of a setting.
     *
     * @param string $name    The name of the setting to get
     * @param mixed  $default
     *
     * @return string
     */
    public function getSetting($name, $default = null)
    {
        if (!App::$container) {
            App::$container = $this;
        }

        $settings = $this->get('deskpro.core.settings');

        return $settings->get($name, $default);
    }

    /**
     * Get the BrandStack.
     *
     * @throws \Throwable
     *
     * @return BrandStack
     */
    public function getBrandStack()
    {
        return $this->get('brand_stack');
    }

    /**
     * Get the value of a brand setting.
     *
     * @param string $name    The name of the setting to get
     * @param mixed  $default
     *
     * @return string
     */
    public function getBrandSetting($name, $default = null)
    {
        return $this->get('brand_stack')->getActive()->getSetting($name, $default);
    }

    /**
     * Get the settings object.
     *
     * @return \Application\DeskPRO\Settings\Settings
     *
     * @deprecated use getSettingsResolver() and use its api instead
     */
    public function getSettingsHandler()
    {
        $settings = $this->get('deskpro.core.settings');

        return $settings;
    }

    /**
     * Get the settings resolver system service.
     *
     * @return \Application\DeskPRO\NewSettings\SettingsResolver
     */
    public function getSettingsResolver()
    {
        $settings = $this->getSystemService('settings_resolver');

        return $settings;
    }

    /**
     * @return \Application\DeskPRO\DependencyInjection\SystemServices\AgentDataService
     *
     * @deprecated Avoid using it as much as possible. It works really slow if we have many agents/usergroups/teams
     * because it pre loads them ALL, even if we need something just for one agent
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
     * @deprecated
     *
     * @return string
     */
    public function getLogDir()
    {
        return $this->get('deskpro.app_env')->getUserLogsDir();
    }

    /**
     * @deprecated
     *
     * @return string
     */
    public function getBlobDir()
    {
        return $this->get('deskpro.app_env')->getUserFilesDir();
    }

    /**
     * * @deprecated
     *
     * @return string
     */
    public function getBackupDir()
    {
        return $this->get('deskpro.app_env')->getUserBackupsDir();
    }

    /**
     * Checks a static security token.
     *
     * @param string $name
     * @param string $token
     *
     * @return bool
     */
    public function checkStaticSecurityToken($name, $token)
    {
        return Util::checkStaticSecurityToken($token, md5($this->getSetting('core.app_secret', 'secret').$name));
    }

    /**
     * Generate static security token.
     *
     * @param string $name
     * @param int    $timeout
     *
     * @return string
     */
    public function generateStaticSecurityToken($name, $timeout = 18000)
    {
        return Util::generateStaticSecurityToken(md5($this->getSetting('core.app_secret', 'secret').$name), $timeout);
    }

    /**
     * @return \Application\DeskPRO\App\AppManager
     */
    public function getAppManager()
    {
        return $this->get('deskpro.apps.manager');
    }

    /**
     * @return AgentAppPermissions
     */
    public function getAppPerms()
    {
        if (!$this->agent_app_perms) {
            $this->agent_app_perms = AgentAppPermissions::newFromDb($this->getDb(), $this->getAppManager()->getAllApps());
        }

        return $this->agent_app_perms;
    }

    /**
     * @return \Application\DeskPRO\Tickets\Actions\ActionDef\TicketActionDefManager
     */
    public function getTicketActionDefManager()
    {
        return $this->getSystemService('ticket_action_def_manager');
    }

    /**
     * @return \Application\DeskPRO\Service\CustomFieldManager
     */
    public function getCustomFieldManager()
    {
        return $this->get('dp.custom_fields.manager');
    }
}
