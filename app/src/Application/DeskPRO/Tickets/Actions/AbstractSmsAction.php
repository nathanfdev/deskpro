<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * @category Sms
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\AppInstance;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\SnippetFormatter;
use Orb\Sms\SmsMessage;
use Orb\Util\Util;

abstract class AbstractSmsAction extends AbstractContainerAwareAction implements ActionInterface, AppActionInterface
{
    /**
     * All children of this class need to construct their own provider from their config
     *
     * @return \Orb\Sms\SmsProviderInterface
     */
    abstract public function getSmsProvider();

    /**
     * If your provider needs a "from" address to work, return a string. Otherwise, it's ok to return null.
     *
     * @return string|null
     */
    abstract public function getFromPhoneNumber();

    /**
     * @var
     */
    protected $app;

    /**
     * {@inheritDoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        if (!$this->getApp()) {
            $context->getLogger()->debug(
                sprintf('[%s] No app (app id: %d)', $this->getActionType(), $this->getMetaData()->get('app_id'))
            );
        }

        ###########################################################################
        # Prepare the DeskproSmsSender
        ###########################################################################
        $sms_sender = $this->getContainer()->getSystemService('sms_sender');
        $sms_sender->setDefaultProvider($this->getSmsProvider());
        $sms_sender->setDefaultFromNumber($this->getFromPhoneNumber());

        ###########################################################################
        # Format and prepare text message
        ###########################################################################
        $action_message_template = $this->getActionOption('message');
        $formatter = new SnippetFormatter($this->getContainer()->getTwig());
        $formatter->addVar('user_vars', $context->getUserVars());
        $message = $formatter->formatText($action_message_template, $ticket);

        ###########################################################################
        # Gather Raw Numbers
        ###########################################################################
        $numbers = array();
        $numbers[] = $this->getActionOption('to_number');

        ###########################################################################
        # Gather Agent IDs - from agent and team selections
        # TODO: This stuff should almost certainly be more DRY and abstract for other actions to use.
        ###########################################################################
        $agents = array();
        $agent_ids = $this->getActionOption('agent_ids', array());
        foreach ($agent_ids as $aid) {
            if ($aid == 'assigned') {
                if ($ticket->agent) {
                    $agents[] = $ticket->agent->getId();
                }
            } elseif ($aid == 'followers') {
                if ($agent_followers = $ticket->getAgentParticipants()) {
                    foreach ($agent_followers as $agent) {
                        $agents[] = $agent->getId();
                    }
                }
            } elseif ($aid > 0) {
                $agents[] = $aid;
            }
        }
        $team_member_agents = array();
        $team_ids = $this->getActionOption('agent_teams', array());
        foreach ($team_ids as $tid) {
            if ($tid == 'assigned') {
                if ($ticket->agent_team) {
                    foreach ($ticket->agent_team->members as $agent) {
                        $team_member_agents[] = $agent->getId();
                    }
                }
            } elseif ($tid > 0) {
                $team = $this->getContainer()->getEm()->getRepository('DeskPRO:AgentTeam')->find($tid);
                if ($team) {
                    foreach ($team->members as $agent) {
                        $team_member_agents[] = $agent->getId();
                    }
                }
            }
        }
        $department_agent_ids = array();
        $department_ids = $this->getActionOption('department_ids', array());
        $personRepo = $this->getContainer()->getEm()->getRepository('DeskPRO:Person');
        foreach ($department_ids as $did) {
            $department_agents = $personRepo->getAgentsInDepartment($did);
            foreach ($department_agents as $a) {
                $department_agent_ids[] = $a->id;
            }
        }

        #############################################################################
        # Convert Agent IDs into phone numbers - ensure agent is only selected once
        #############################################################################
        $agents = array_merge($agents, $team_member_agents, $department_agent_ids);
        $agents = array_unique($agents);
        $repo = $this->getContainer()->getEm()->getRepository('DeskPRO:Person');
        $agents = $repo->getPeopleResultsFromIds($agents);
        foreach ($agents as $agent) {
            if ($agent->primary_phone_number) {
                $numbers[] = $agent->primary_phone_number_text;
            }
        }
        $numbers = array_unique($numbers);
        $numbers = array_filter($numbers, function ($val) {
            return $val !== null && strlen($val) > 5; // attempt to filter out any impossible numbers
        });

        foreach ($numbers as $number) {
            $this->logSendingTo($number, $context);
            try {
                $sms_message = new SmsMessage($message);
                $result = $sms_sender->send($number, $sms_message);

                if ($result) {
                    $this->recordSuccessfulTicketChange($ticket, $number);
                } else {
                    $this->logErrorSendingTo($result->getProviderMessage(), $context);
                    $this->recordFailedTicketChange($ticket, $number);
                }
            } catch (\Exception $e) {
                $this->logErrorSendingTo($e->getMessage(), $context);
                $this->recordFailedTicketChange($ticket, $number);
            }
        }
    }

    /**
     * @param                          $to_number
     * @param ExecutorContextInterface $context
     */
    protected function logSendingTo($to_number, ExecutorContextInterface $context)
    {
        $context->getLogger()->debug(
            sprintf(
                '[%s] Sending SMS message from "%s" to "%s"',
                $this->getActionType(),
                $this->getFromPhoneNumber(),
                $to_number
            )
        );
    }

    /**
     * @param                          $e
     * @param ExecutorContextInterface $context
     */
    protected function logErrorSendingTo($message, ExecutorContextInterface $context)
    {
        $context->getLogger()->notice("[{$this->getActionType()}] Error sending SMS message: {$message}");
    }

    /**
     * @param Ticket $ticket
     * @param string $to_number
     */
    protected function recordSuccessfulTicketChange(Ticket $ticket, $to_number)
    {
        $recordMsg = sprintf('Sent to %s', $to_number);
        $this->recordTicketChange($ticket, $recordMsg);
    }

    /**
     * @param Ticket $ticket
     * @param string $to_number
     */
    protected function recordFailedTicketChange(Ticket $ticket, $to_number)
    {
        $recordMsg = sprintf('Failed sending to %s', $to_number);
        $this->recordTicketChange($ticket, $recordMsg);
    }

    /**
     * @param Ticket $ticket
     * @param        $recordMsg
     */
    protected function recordTicketChange(Ticket $ticket, $recordMsg)
    {
        $app = $this->getApp();

        $ticket->getStateChangeRecorder()->recordData(
            'app_message',
            array(
                'app_id'        => $app->id,
                'app_title'     => $app->title,
                'package_name'  => $app->package->name,
                'package_title' => $app->package->title,
                'message'       => $recordMsg
            )
        );
    }

    /**
     * @return string
     */
    public function getActionType()
    {
        return Util::getBaseClassname($this).$this->getMetaData()->get('app_id', 0);
    }

    /**
     * @return AppInstance
     */
    protected function getApp()
    {
        if ($this->app !== null) {
            return $this->app;
        }

        $this->app = false;
        $app_manager = $this->getContainer()->getAppManager();
        $app_id = $this->getMetaData()->get('app_id', 0);

        if ($app_manager->hasApp($app_id)) {
            $this->app = $app_manager->getApp($app_id);
        }

        return $this->app === false ? null : $this->app;
    }
}
