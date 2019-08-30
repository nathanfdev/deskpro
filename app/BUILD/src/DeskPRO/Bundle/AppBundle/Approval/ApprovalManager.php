<?php

namespace DeskPRO\Bundle\AppBundle\Approval;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Monolog\Logger as DpLogger;
use Application\DeskPRO\Tickets\Actions\ActionApplicator;
use Application\DeskPRO\Tickets\ExecutorContextVars;
use Application\DeskPRO\Tickets\TicketSaveActions\ErrorCheckedInterface;
use Application\DeskPRO\Tickets\TicketSaveActions\SaveTicketLogs;
use Application\DeskPRO\Tickets\Triggers\TriggerActions;
use DeskPRO\Bundle\ApiBundle\Security\Token\ApiKeySecurityToken;
use DeskPRO\Bundle\AppBundle\Entity\Approval\AbstractBaseApproval;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse;
use DeskPRO\Bundle\AppBundle\Entity\Approval\TicketApprovalInterface;
use Doctrine\ORM\EntityManagerInterface;
use Application\DeskPRO\Tickets\Actions\ActionInterface;
use DpSys\LowError\SystemErrorHandler;
use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Orb\Util\Util as OrbUtil;

/**
 * Class ApprovalManager
 *
 * @package DeskPRO\Bundle\AppBundle\Approval
 */
class ApprovalManager
{
    /**
     * @var DeskproContainer
     */
    private $container;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ExecutorContextVars
     */
    private $autoVars;

    /**
     * ApprovalManager constructor.
     *
     * @param DeskproContainer $container
     */
    public function __construct(DeskproContainer $container)
    {
        $this->container = $container;
        $this->em = $container->getEm();

        $this->autoVars = new ExecutorContextVars();
        $this->autoVars->setCustomFieldManager($container->getCustomFieldManager());
        $this->autoVars->setTicketFieldManager($container->getTicketFieldManager());
    }

    /**
     * @param AbstractBaseApproval $approval
     * @param ExecutorContext $context
     * @throws Exception
     */
    public function saveApproval(AbstractBaseApproval $approval, ExecutorContext $context)
    {
        $this->em->transactional(function (EntityManagerInterface $em) use ($approval, $context) {
            $isNew = !$em->contains($approval);

            $approval->notifyAssociationChanges($em);
            $em->persist($approval);

            if ($isNew) {
                $context->setEventType(ExecutorContext::EVENT_ON_CREATE);
                $context->setApproval($approval);
                $this->applyActions(
                    $approval,
                    $context,
                    'getActionsOnCreate'
                );
            }
        });
    }

    /**
     * @param AbstractBaseApproval $approval
     * @param ExecutorContext $context
     * @throws Exception
     */
    public function cancelApproval(AbstractBaseApproval $approval, ExecutorContext $context)
    {
        $this->em->transactional(function (EntityManagerInterface $em) use ($approval, $context) {
            $approval->cancel();

            $approval->notifyAssociationChanges($em);
            $em->persist($approval);

            $context->setEventType(ExecutorContext::EVENT_ON_CANCEL);
            $context->setApproval($approval);

            $this->applyActions(
                $approval,
                $context,
                'getActionsOnCancel'
            );
        });
    }

    /**
     * @param AbstractBaseApproval $approval
     * @param ApprovalResponse $response
     * @param ExecutorContext $context
     * @throws Exception
     */
    public function addApprovalResponse(AbstractBaseApproval $approval, ApprovalResponse $response, ExecutorContext $context)
    {
        $this->em->transactional(function (EntityManagerInterface $em) use ($approval, $response, $context) {

            $approval->addResponse($response);

            $approval->notifyAssociationChanges($em);
            $em->persist($approval);

            $context->setApproval($approval);
            $context->setApprovalResponse($response);

            if ($approval->isComplete()) {
                if ($approval->isStatus(AbstractBaseApproval::STATUS_APPROVED)) {
                    $context->setEventType(ExecutorContext::EVENT_ON_APPROVED);
                    $this->applyActions(
                        $approval,
                        $context,
                        'getActionsOnApproved'
                    );
                } elseif ($approval->isStatus(AbstractBaseApproval::STATUS_REJECTED)) {
                    $context->setEventType(ExecutorContext::EVENT_ON_REJECTED);
                    $this->applyActions(
                        $approval,
                        $context,
                        'getActionsOnRejected'
                    );
                }
            } else {
                if ($response->isApproved()) {
                    $context->setEventType(ExecutorContext::EVENT_ON_PARTIAL_APPROVAL_RESPONSE);
                    $this->applyActions(
                        $approval,
                        $context,
                        'getActionsOnPartialApprovalResponse'
                    );
                } elseif ($response->isRejected()) {
                    $context->setEventType(ExecutorContext::EVENT_ON_PARTIAL_REJECTION_RESPONSE);
                    $this->applyActions(
                        $approval,
                        $context,
                        'getActionsOnPartialRejectionResponse'
                    );
                }
            }
        });
    }

    /**
     * @param AbstractBaseApproval $approval
     * @param ExecutorContext $context
     * @param string $getActionsMethod
     */
    private function applyActions(AbstractBaseApproval $approval, ExecutorContext $context, $getActionsMethod)
    {
        $applicator = new ActionApplicator($this->container);
        if ($approval instanceof TicketApprovalInterface) {
            /** @var TriggerActions $actions */
            $actions = $approval->{$getActionsMethod}();

            /** @var ActionInterface $action */
            foreach ($actions as $action) {
                $context->getLogger()->info(
                    sprintf('[ApprovalManager:%s] %s', $context->getEventType(), OrbUtil::getBaseClassname($action))
                );
                if ($action instanceof ErrorCheckedInterface) {
                    try {
                        $applicator->apply($action, $approval->getTicket(), $context);
                    } catch (\Exception $e) {
                        SystemErrorHandler::logException($e);
                        $context->getLogger()->error(
                            sprintf('[%s] Exception: %s', OrbUtil::getBaseClassname($action), $e->getMessage())
                        );
                    }
                } else {
                    $applicator->apply($action, $approval->getTicket(), $context);
                }
            }

            (new SaveTicketLogs($this->em))
                ->processTicket($approval->getTicket(), $context)
            ;
        }
    }

    /**
     * @param string $eventMethod
     * @param Person|null $person
     * @param array $eventMethodOptions
     * @return ExecutorContext
     * @throws Exception
     */
    public function createContext($eventMethod, Person $person = null, array $eventMethodOptions = [])
    {
        $eventPerformer = 'system';

        $context = new ExecutorContext($this->createNewLogger());
        $this->autoVars->configureContext($context);

        if ($person) {
            $context->setPersonContext($person);
            $eventPerformer = $person->isAgent()
                ? 'agent'
                : 'user'
            ;
        }

        if ($eventMethod === 'api') {
            $eventMethodOptions['api_v2'] = true;
            $key = null;
            try {
                $token = $this->container->get('security.token_storage')->getToken();
                if ($token instanceof ApiKeySecurityToken) {
                    $credentials = $token->getCredentials();
                    if ($credentials && strpos($credentials, ':') !== false) {
                        list($key) = explode(':', $credentials, 2);
                        $key = (int) $key;
                    }
                }
            } catch (Exception $e) {
            }

            if ($key) {
                $context->getVars()->set('via_api_key', $key);
            }
        }

        $context->setEventPerformer($eventPerformer);
        $context->setEventMethod($eventMethod, $eventMethodOptions);

        return $context;
    }

    /**
     * @return Logger
     * @throws Exception
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
}
