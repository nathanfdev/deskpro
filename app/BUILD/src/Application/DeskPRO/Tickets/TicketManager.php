<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\AppInstance;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Sla;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\EntityRepository\AbstractEntityRepository;
use Application\DeskPRO\Monolog\Logger as DpLogger;
use Application\DeskPRO\Tickets\Actions\ActionApplicator;
use Application\DeskPRO\Tickets\Actions\SendAgentAlert;
use Application\DeskPRO\Tickets\Actions\SendAgentMention;
use Application\DeskPRO\Tickets\Slas\SlaClientMessageSender;
use DeskPRO\Bundle\ApiBundle\Security\Token\ApiKeySecurityToken;
use DeskPRO\Bundle\AppBundle\Notification\Event\Ticket\TicketUpdatedEvent;
use DeskPRO\Bundle\AppBundle\Notification\NotificationEventManager;
use DpSys\LowError\SystemErrorHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Orb\Util\DpStrings;
use Orb\Util\Strings;
use Orb\Util\Util as OrbUtil;
use Symfony\Component\DependencyInjection\Exception\InactiveScopeException;

/**
 * Class TicketManager.
 */
class TicketManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $db;

    /**
     * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    private $container;

    /**
     * @var \Application\DeskPRO\Tickets\TicketSaveActions\TicketSaveActionInterface[]
     */
    private $save_actions;

    /**
     * @var \Application\DeskPRO\Tickets\TicketSaveActions\TicketSaveActionInterface[]
     */
    private $post_save_actions;

    /**
     * @var \Application\DeskPRO\BlobStorage\DeskproBlobStorage
     */
    private $blob_storage;

    /**
     * @var NotificationEventManager
     */
    private $notificationEventManager;


    /** @var ExecutorContextVars */
    private $autoVars;

    /**
     * Constructor.
     *
     * @param DeskproContainer $container
     */
    public function __construct(DeskproContainer $container)
    {
        $this->container                = $container;
        $this->em                       = $container->getEm();
        $this->db                       = $container->getDb();
        $this->blob_storage             = $container->getBlobStorage();
        $this->eventDispatcher          = $container->get('event_dispatcher');
        $this->notificationEventManager = $container->get('deskpro.notification.event_manager');

        /** @var \Application\DeskPRO\EntityRepository\Organization $organizationRepo */
        $organizationRepo = $this->em->getRepository(Organization::class);
        /** @var \Application\DeskPRO\EntityRepository\Brand $brandRepo */
        $brandRepo = $this->em->getRepository(Brand::class);
        /** @var \Application\DeskPRO\EntityRepository\TicketTrigger $ticketTriggerRepo */
        $ticketTriggerRepo = $this->em->getRepository(TicketTrigger::class);

        $this->save_actions      = [];
        $this->post_save_actions = [];

        $this->save_actions[] = new TicketSaveActions\VerifyCreationSystem();
        $this->save_actions[] = new TicketSaveActions\VerifyRef($container->getRefGenerator());
        $this->save_actions[] = new TicketSaveActions\VerifyOrgManagers($organizationRepo);
        $this->save_actions[] = new TicketSaveActions\VerifyAgent();
        $this->save_actions[] = new TicketSaveActions\DetectAutoresponders(
            $this->em,
            $container->getSetting('core_email.antiflood_newtickets'),
            $container->getSetting('core_email.antiflood_newtickets_time'),
            $container->getSetting('core_email.antiflood_newreplies'),
            $container->getSetting('core_email.antiflood_newreplies_time')
        );

        $this->post_save_actions[] = new TicketSaveActions\SaveContextualFields($container->getCustomFieldManager());
        $this->post_save_actions[] = new TicketSaveActions\ExecTriggers($ticketTriggerRepo, new ActionApplicator($container));
        $this->post_save_actions[] = new TicketSaveActions\VerifyBrand(
            $brandRepo,
            $container->getSetting('portal.default_brand')
        );
        $this->post_save_actions[] = new TicketSaveActions\VerifyDepartment(
            $container->getTicketDepartments(),
            $container->get('brand_form_helper')
        );
        $this->post_save_actions[] = new TicketSaveActions\SetActionTimes();
        $this->post_save_actions[] = new TicketSaveActions\ApplySlas(
            $this->em->getRepository(Sla::class)->getAutoSlas(),
            $this->em,
            new SlaClientMessageSender($this->db, $this->eventDispatcher)
        );
        $this->post_save_actions[] = new TicketSaveActions\RecalculateSlas(
            $this->em,
            new ActionApplicator($container),
            $this->eventDispatcher
        );
        $this->post_save_actions[] = new TicketSaveActions\SaveTicketLogs($this->em);
        $this->post_save_actions[] = new TicketSaveActions\RunFilterUpdates($container);
        $this->post_save_actions[] = new TicketSaveActions\RecalculateTicketStats($this->db);
        $this->post_save_actions[] = new TicketSaveActions\ZapierWebHook($this->em, $container->get('serializer'));
        $this->post_save_actions[] = new TicketSaveActions\CancelFollowUpOnUserReply($this->em);

        $this->autoVars = new ExecutorContextVars();
        $this->autoVars->setCustomFieldManager($container->getCustomFieldManager());
        $this->autoVars->setTicketFieldManager($container->getTicketFieldManager());
    }

    /**
     * Clears auto context vars.
     */
    public function clearAutoContextVars()
    {
        $this->autoVars = new ExecutorContextVars();
    }

    /**
     * Adds an array of vars to auto context vars.
     *
     * @param array $vars
     */
    public function addAutoContextVars(array $vars)
    {
        $this->autoVars->setAll($vars);
    }

    /**
     * Gets array of currently set context vars.
     *
     * @return array
     */
    public function getAutoContextVars()
    {
        return $this->autoVars->toArray();
    }

    /**
     * Set an auto context var.
     *
     * @param string $k
     * @param mixed  $v
     */
    public function setAutoContextVar($k, $v)
    {
        $this->autoVars->set($k, $v);
    }

    /**
     * Unset an auto context var.
     *
     * @param string $k
     */
    public function unsetAutoContextVar($k)
    {
        $this->autoVars->unsetVar($k);
    }

    /**
     * Create a new ticket object. When you are ready to persist it, call saveTicket().
     *
     * @return Ticket
     */
    public function createTicket()
    {
        $ticket = new Ticket();
        $ticket->disableAutoTicketProcess();

        // Generate a ref now
        // This will cause less locking if we are outside of a transaction
        $ref_gen = $this->container->getRefGenerator();
        try {
            $ticket->ref = $ref_gen->generateReference(Ticket::class);
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);
            $ref         = DpStrings::random(4, Strings::CHARS_ALPHA_IU).'-'.DpStrings::random(4, Strings::CHARS_NUM).'-'.DpStrings::random(4, Strings::CHARS_ALPHA_IU).'-'.date('ymd');
            $ticket->ref = $ref;
        }

        $ticket->__dp_is_autogen_ref = false;

        return $ticket;
    }

    /**
     * Finds a ticket and returns it.
     *
     * NOTE: This will disable auto-ticket processing,
     * which means if you make changes, you need to use the saveTicket() method to
     * have those changes run the other related systems (like triggers etc).
     *
     * @param int $id
     *
     * @return \Application\DeskPRO\Entity\Ticket
     */
    public function getTicket($id)
    {
        $ticket = $this->em->find(Ticket::class, $id);
        if ($ticket) {
            $ticket->disableAutoTicketProcess();
        }

        return $ticket;
    }

    /**
     * Finds all tickets and returns them.
     *
     * NOTE: This will disable auto-ticket processing,
     * which means if you make changes, you need to use the saveTicket() method to
     * have those changes run the other related systems (like triggers etc).
     *
     * @param int[]|string[] $ids
     *
     * @return \Application\DeskPRO\Entity\Ticket[]|array
     */
    public function getTickets($ids)
    {
        /** @var AbstractEntityRepository $ticketRepository */
        $ticketRepository = $this->em->getRepository(Ticket::class);
        if ($ticketRepository instanceof AbstractEntityRepository) {
            /** @var Ticket[] $tickets */
            $tickets = $ticketRepository->getByIds($ids);
            foreach ($tickets as $ticket) {
                $ticket->disableAutoTicketProcess();
            }

            return $tickets;
        }

        throw new \RuntimeException('unexpected repository type');
    }

    /**
     * Disables auto-ticket processing on the ticket. This means you should save the ticket
     * via $this->saveTicket().
     *
     * This is used when interacting with legacy code where ticket processing is expected to happen
     * automatically.
     *
     * @param Ticket $ticket
     */
    public function markAsManaged(Ticket $ticket)
    {
        $ticket->disableAutoTicketProcess();
    }

    /**
     * Re-enables auto-ticket processing on the ticket.
     *
     * This is used when interacting with legacy code where ticket processing is expected to happen
     * automatically.
     *
     * @param Ticket $ticket
     */
    public function markAsUnmanaged(Ticket $ticket)
    {
        $ticket->enableAutoTicketProcess();
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     *
     * @throws \Exception
     */
    public function saveTicket(Ticket $ticket, ExecutorContextInterface $context)
    {
        if (isset($GLOBALS['DP_IS_IMPORTING'])) {
            $this->em->persist($ticket);
            $this->em->flush();

            $this->notificationEventManager->deliver();

            return;
        }

        $this->db->beginTransaction();

        try {
            $ret = $this->doSaveTicket($ticket, $context);
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            $this->notificationEventManager->deliver(true);
            throw $e;
        }

        $this->notificationEventManager->deliver(true);

        return $ret;
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     */
    private function doSaveTicket(Ticket $ticket, ExecutorContextInterface $context)
    {
        // Noop is sometimes used when we need to save a ticket and have appropriate client-messages
        // sent to update agent filters, but we dont want the usual triggers etc to run.
        // This is usually done when the ticket is being deleted.
        $is_noop           = $context->getEventType() == 'noop';
        $is_trivial_change = $ticket->getStateChangeRecorder()->isTrivialChangeSet();

        $time_start = microtime(true);
        $context->getLogger()->info(sprintf('########## START SAVE TICKET -- %s ##########', $ticket->getId() ? $ticket->getId() : 'newticket'));

        $context->getLogger()->debug(sprintf('EventType: %s', $context->getEventType()));
        $context->getLogger()->debug(sprintf('EventMethod: %s', $context->getEventMethod()));
        $context->getLogger()->debug(sprintf('EventPerformer: %s', $context->getEventPerformer()));
        $context->getLogger()->debug(sprintf('StateChanges: %s', implode(', ', $ticket->getStateChangeRecorder()->getChangedFields())));

        if ($is_trivial_change) {
            $context->getLogger()->debug('is_trivial_change = true');
            $context->setEventType('noop');
            $is_noop = true;
        }

        $contextPerson = $context->getPersonContext();
        if ($contextPerson) {
            $context->getLogger()->debug(sprintf(
                'PersonContext: <Person:%d> %s %s',
                $contextPerson->getId(),
                $contextPerson->getDisplayName(),
                $contextPerson->getPrimaryEmailAddress()
            ));
        } else {
            $context->getLogger()->debug('PersonContext: NULL');
        }

        $this->em->persist($ticket);

        //----------------------------------------
        // Set the creation system
        //----------------------------------------

        if (!$is_noop) {
            $agent_alert_action = new SendAgentMention();
            $agent_alert_action->setContainer($this->container);
            $agent_alert_action->applyAction($ticket, $context);
        }

        foreach ($this->save_actions as $action) {
            $context->getLogger()->info(sprintf('[TicketManager:saveaction] %s', OrbUtil::getBaseClassname($action)));
            if ($action instanceof TicketSaveActions\ErrorCheckedInterface) {
                try {
                    $action->processTicket($ticket, $context);
                } catch (\Exception $e) {
                    SystemErrorHandler::logException($e);
                    $context->getLogger()->error(sprintf('[%s] Exception: %s', OrbUtil::getBaseClassname($action), $e->getMessage()));
                }
            } else {
                $action->processTicket($ticket, $context);
            }
        }

        if (!$is_noop && !$ticket->getTicketHash()) {
            $ticket->recomputeHash();
        }

        $this->em->persist($ticket);
        $this->em->flush();
        $this->autoVars->getCustomFieldManager()->flush();

        foreach ($this->post_save_actions as $action) {
            $context->getLogger()->info(sprintf('[TicketManager:postsaveaction] %s', OrbUtil::getBaseClassname($action)));
            if ($action instanceof TicketSaveActions\ErrorCheckedInterface) {
                try {
                    $action->processTicket($ticket, $context);
                } catch (\Exception $e) {
                    SystemErrorHandler::logException($e);
                    $context->getLogger()->error(sprintf('[%s] Exception: %s', OrbUtil::getBaseClassname($action), $e->getMessage()));
                }
            } else {
                $action->processTicket($ticket, $context);
            }
            $this->em->persist($ticket);
            $this->em->flush();
        }

        if (!$is_noop) {
            $logs = $context->getVars()->get('ticket_logs', []);

            $agent_alert_action = new SendAgentAlert([
                'agent_ids'   => ['notify_list'],
                'ticket_logs' => $logs,
            ]);
            $agent_alert_action->setContainer($this->container);
            $agent_alert_action->applyAction($ticket, $context);
        }

        if (!$is_trivial_change) {
            $this->eventDispatcher->dispatch(TicketUpdatedEvent::EVENT_NAME, new TicketUpdatedEvent(
                'agent.ticket-updated',
                [
                    'ticket_id'      => $ticket->getId(),
                    'changed_fields' => $ticket->getStateChangeRecorder()->getChangedFields(),
                    'via_person'     => $context->getPersonContext() ? $context->getPersonContext()->getId() : null,
                ]
            ));
        }

        if ($ticket->getStateChangeRecorder()->hasChangedField('locked_by_agent')) {
            $lockedByAgent = $ticket->getLockedByAgent();

            $this->eventDispatcher->dispatch(TicketUpdatedEvent::EVENT_NAME, new TicketUpdatedEvent(
                'agent-notification.tickets.locked-status',
                [
                    'ticket_id'      => $ticket->getId(),
                    'is_locked'      => (bool) $lockedByAgent,
                    'locked_by'      => $lockedByAgent ? $lockedByAgent->getId() : null,
                    'locked_by_name' => $lockedByAgent ? $lockedByAgent->getDisplayName() : null,
                    'via_person'     => $context->getPersonContext() ? $context->getPersonContext()->getId() : null,
                ]
            ));
        }

        if (!$is_noop) {
            $search_updater = new TicketSearchUpdater($this->db, $ticket);
            \DpShutdown::add(
                function () use ($search_updater) {
                    try {
                        $search_updater->update();
                    } catch (\Exception $e) {
                        SystemErrorHandler::logException($e);
                    }
                }, null, 'db_done_trans_commit'
            );
        }

        $this->em->flush();

        //----------------------------------------
        // Done
        //----------------------------------------

        $context->getLogger()->info(sprintf('########## END SAVE TICKET -- %s -- %.4fs ##########', $ticket->getId() ?: 0, microtime(true) - $time_start));

        if (!$is_noop && $ticket->getStatusCode() != 'hidden.deleted' && $context->getLogger() instanceof DpLogger) {
            $log_text = $context->getLogger()->getSavedMessages();
            if ($log_text) {
                try {
                    $blob = $this->blob_storage->createBlobRecordFromString(
                        $log_text,
                        'ticket-manager.'.date('Y-m-d.H-i-s').'.'.Strings::random(4, Strings::CHARS_ALPHA_IU).'.log',
                        'plain/text',
                        ['tag' => 'logs.ticket_proc_log', 'prefer_gzipped' => true]
                    );
                } catch (\Exception $e) {
                    $blob = null;
                    SystemErrorHandler::logException($e);
                }

                if ($blob) {
                    try {
                        $this->db->insert('ticket_proc_log', [
                            'ticket_id'    => $ticket->getId(),
                            'blob_id'      => $blob->getId(),
                            'date_created' => date('Y-m-d H:i:s'),
                        ]);
                    } catch (\Exception $e) {
                        SystemErrorHandler::logException($e);
                    }
                }
            }
        }

        $ticket->resetStateChangeRecorder();
        $ticket->__dp_last_process_save = $ticket->getStateChangeRecorder()->getStateVersion();
    }

    /**
     * @param Person $agent
     * @param        $eventType
     * @param        $eventMethod
     * @param array  $eventMethodOptions
     *
     * @return ExecutorContextInterface
     */
    public function createAgentExecutorContext(Person $agent = null, $eventType, $eventMethod, array $eventMethodOptions = [])
    {
        return $this->createPersonContext('agent', $agent, $eventType, $eventMethod, $eventMethodOptions);
    }

    /**
     * @param Person $user
     * @param        $eventType
     * @param        $eventMethod
     * @param array  $eventMethodOptions
     *
     * @return ExecutorContextInterface
     */
    public function createUserExecutorContext(Person $user = null, $eventType, $eventMethod, array $eventMethodOptions = [])
    {
        return $this->createPersonContext('user', $user, $eventType, $eventMethod, $eventMethodOptions);
    }

    /**
     * @param string $event_type
     * @param string $event_method
     * @param array  $event_method_options
     *
     * @return ExecutorContextInterface
     */
    public function createSystemExecutorContext($event_type = 'system', $event_method = 'system', array $event_method_options = [])
    {
        $context = new ExecutorContext($this->createNewLogger());
        $this->autoVars->configureContext($context);

        $context->setEventType($event_type);
        $context->setEventMethod($event_method, $event_method_options);

        return $context;
    }

    /**
     * @param AppInstance $app
     * @param string      $event_method
     * @param array       $event_method_options
     *
     * @return ExecutorContext
     */
    public function createAppExecutorContext(AppInstance $app, $event_method = 'general', array $event_method_options = [])
    {
        $context = new ExecutorContext($this->createNewLogger());
        $this->autoVars->configureContext($context);

        $context->setEventType('update');
        $context->setEventMethod($app->package->name.'.'.$app->id.'.'.$event_method, $event_method_options);

        return $context;
    }

    /**
     * @return Logger
     */
    protected function createNewLogger()
    {
        $logger = new DpLogger('tickets');
        $logger->enableSavedMessages();

        $env = $this->container->get('deskpro.app_env');

        if ($logfile = $env->getConfig('logs.enable_ticket_log')) {
            if ($logfile === true || $logfile === 1 || $logfile === '1' || $logfile === 'true') {
                $logfile = $env->getUserLogsDir().'/ticket.log';
            }
            $stream = new StreamHandler($logfile);
            $logger->pushHandler($stream);
        }

        if (defined('DP_INTERFACE') && DP_INTERFACE == 'cli' && empty($GLOBALS['DP_CRON_ID']) && (in_array('--verbose', $_SERVER['argv']) || in_array('-v', $_SERVER['argv']))) {
            $stream = new StreamHandler('php://stdout');
            $logger->pushHandler($stream);
        }

        return $logger;
    }

    /**
     * @param string      $eventPerformer
     * @param Person|null $person
     * @param string      $eventType
     * @param string      $eventMethod
     * @param array       $eventMethodOptions
     *
     * @return ExecutorContext
     */
    private function createPersonContext($eventPerformer, Person $person = null, $eventType, $eventMethod, array $eventMethodOptions = [])
    {
        $context = new ExecutorContext($this->createNewLogger());
        $this->autoVars->configureContext($context);

        if ($person) {
            $context->setPersonContext($person);
        }

        if ($eventMethod === 'api') {
            $key = null;

            // try to get legacy api key
            try {
                $auth = $this->container->get('deskpro.api.request_auth');
                if ($auth && $auth->getApiUser()) {
                    $key = $auth->getApiUser()->api_key ? $auth->getApiUser()->api_key->getId() : null;
                }
            } catch (InactiveScopeException $e) {
            }

            // try to get new api key
            try {
                $token = $this->container->get('security.token_storage')->getToken();
                if ($token instanceof ApiKeySecurityToken) {
                    $credentials = $token->getCredentials();
                    if ($credentials && strpos($credentials, ':') !== false) {
                        list($key) = explode(':', $credentials, 2);
                        $key       = (int) $key;
                    }
                }
            } catch (InactiveScopeException $e) {
            }

            if ($key) {
                $context->getVars()->set('via_api_key', $key);
            }
        }

        $context->setEventPerformer($eventPerformer);
        $context->setEventType($eventType);
        $context->setEventMethod($eventMethod, $eventMethodOptions);

        return $context;
    }
}
