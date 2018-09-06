<?php

/**
 * DeskPRO.
 *
 * @category Install
 */

namespace Application\InstallBundle\Data\DefaultData;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\Tickets\Actions\SendAgentEmail;
use Application\DeskPRO\Tickets\Actions\SendAgentNewEmail;
use Application\DeskPRO\Tickets\Actions\SendSpecificUserEmail;
use Application\DeskPRO\Tickets\Actions\SendUserEmail;
use Application\DeskPRO\Tickets\Actions\SendUserNewEmail;
use Application\DeskPRO\Tickets\Actions\SetAgent;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckAgent;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckAgentMessage;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckEmailCcAdded;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckUserIsEmailed;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckUserMessage;
use Application\DeskPRO\Tickets\Triggers\Terms\TriggerTermComposite;

/**
 * Class TriggerData.
 */
class TriggerData extends AbstractDefaultData
{
    /**
     * {@inheritdoc}
     */
    public function runInstall()
    {
        $this->installTriggerRecords();
    }

    /**
     * {@inheritdoc}
     */
    public function runReset()
    {
        $this->getDb()->executeUpdate('DELETE FROM ticket_triggers WHERE sys_name IS NOT NULL');
        $this->runInstall();
    }

    /**
     * {@inheritdoc}
     */
    public function runSync()
    {
        $exist_names = $this->getDb()->fetchAllCol('SELECT sys_name FROM ticket_triggers WHERE sys_name IS NOT NULL');
        $this->installTriggerRecords($exist_names);
    }

    private function installTriggerRecords(array $ignore = [])
    {
        $ignore = array_fill_keys($ignore, true);

        $features = App::getContainer()->get('deskpro.feature_flags');
        $features->_setSettingsResolver(App::getContainer()->get('settings_resolver'));

        $newEmailTemplates = $features->hasBeta('email_templates');

        //-----
        // Send agent notifications
        //-----

        foreach ([
            'newticket' => 'DeskPRO:emails_agent:ticket-new.html.twig',
            'newreply' => 'DeskPRO:emails_agent:ticket-reply.html.twig',
            'update' => 'DeskPRO:emails_agent:ticket-update.html.twig',
        ] as $eventTrigger => $templateName) {
            $trigger = new TicketTrigger();
            $trigger->setEventTrigger($eventTrigger);
            $trigger->setByUserMode(['api', 'email', 'form', 'portal', 'widget']);
            $trigger->setByAgentMode(['api', 'email', 'web', 'mobile']);
            $trigger->setRunOrder(1000);
            $trigger->setSysName("default_{$eventTrigger}_agentemail");
            $trigger->setTitle('Send agent notifications');
            if ($newEmailTemplates) {
                $trigger->actions->addAction(
                    new SendAgentNewEmail(
                        [
                            'template'  => $templateName,
                            'agent_ids' => ['notify_list'],
                            'from_name' => 'performer',
                        ]
                    )
                );
            } else {
                $trigger->actions->addAction(
                    new SendAgentEmail(
                        [
                            'template'  => $templateName,
                            'agent_ids' => ['notify_list'],
                            'from_name' => 'performer',
                        ]
                    )
                );
            }

            if (!isset($ignore[$trigger->getSysName()])) {
                $this->getEm()->persist($trigger);
            }
        }

        //-----
        // newticket: Send user new ticket by agent
        //-----

        $trigger = new TicketTrigger();
        $trigger->setEventTrigger('newticket');
        $trigger->setRunOrder(1000);
        $trigger->setByAgentMode(['api', 'email', 'web', 'mobile']);
        $trigger->setIsEnabled(true);
        $trigger->setSysName('default_newticket_byagent');
        $trigger->setTitle('Send user new ticket by agent');

        $set = new TriggerTermComposite();
        $set->add(new CheckAgentMessage('isset', ['message' => '']));
        $set->add(new CheckUserIsEmailed('not'));
        $set->setOperator('AND');
        $trigger->terms->addTerm($set);

        if ($newEmailTemplates) {
            $trigger->actions->addAction(
                new SendUserNewEmail(
                    [
                        'template'    => 'SendmailBundle:emails_user:ticket_new_by_agent.html.twig',
                        'do_cc_users' => true,
                        'from_name'   => 'performer',
                    ]
                )
            );
        } else {
            $trigger->actions->addAction(new SendUserEmail([
                'template'    => 'DeskPRO:emails_user:ticket-new-byagent.html.twig',
                'do_cc_users' => true,
                'from_name'   => 'performer',
            ]));
        }

        if (!isset($ignore[$trigger->getSysName()])) {
            $this->getEm()->persist($trigger);
        }

        //-----
        // newticket: Send user auto-reply
        //-----

        $trigger = new TicketTrigger();
        $trigger->setEventTrigger('newticket');
        $trigger->setRunOrder(1000);
        $trigger->setByUserMode(['api', 'email', 'form', 'portal', 'widget']);
        $trigger->setIsEnabled(false);
        $trigger->setSysName('default_newticket_userautoreply');
        $trigger->setTitle('Send auto-reply confirmation to user');

        $set = new TriggerTermComposite();
        $set->add(new CheckUserIsEmailed('not'));
        $set->setOperator('AND');
        $trigger->terms->addTerm($set);

        if ($newEmailTemplates) {
            $trigger->actions->addAction(
                new SendUserNewEmail(
                    [
                        'template'    => 'SendmailBundle:emails_user:ticket_new_autoreply.html.twig',
                        'do_cc_users' => true,
                        'from_name'   => 'helpdesk_name',
                    ]
                )
            );
        } else {
            $trigger->actions->addAction(new SendUserEmail([
                'template'    => 'DeskPRO:emails_user:ticket-new-autoreply.html.twig',
                'do_cc_users' => true,
                'from_name'   => 'helpdesk_name',
            ]));
        }

        if (!isset($ignore[$trigger->getSysName()])) {
            $this->getEm()->persist($trigger);
        }

        //-----
        // newreply: Send user auto-reply
        //-----

        $trigger = new TicketTrigger();
        $trigger->setEventTrigger('newreply');
        $trigger->setRunOrder(1000);
        $trigger->setByUserMode(['api', 'email', 'form', 'portal', 'widget']);
        $trigger->setIsEnabled(false);
        $trigger->setSysName('default_newreply_userautoreply');
        $trigger->setTitle('Send auto-reply confirmation to user');

        $set = new TriggerTermComposite();
        $set->add(new CheckUserIsEmailed('not'));
        $set->setOperator('AND');
        $trigger->terms->addTerm($set);

        if ($newEmailTemplates) {
            $trigger->actions->addAction(
                new SendUserNewEmail(
                    [
                        'template'    => 'SendmailBundle:emails_user:ticket_reply_autoreply.html.twig',
                        'do_cc_users' => true,
                        'from_name'   => 'helpdesk_name',
                    ]
                )
            );
        } else {
            $trigger->actions->addAction(new SendUserEmail([
                'template'    => 'DeskPRO:emails_user:ticket-reply-autoreply.html.twig',
                'do_cc_users' => true,
                'from_name'   => 'helpdesk_name',
            ]));
        }

        if (!isset($ignore[$trigger->getSysName()])) {
            $this->getEm()->persist($trigger);
        }

        //-----
        // newreply: Send user new reply from agent
        //-----

        $trigger = new TicketTrigger();
        $trigger->setEventTrigger('newreply');
        $trigger->setRunOrder(1000);
        $trigger->setByAgentMode(['api', 'email', 'web', 'mobile']);
        $trigger->setIsEnabled(true);
        $trigger->setSysName('default_newreply_fromagent');
        $trigger->setTitle('Send user new reply from agent');

        $set = new TriggerTermComposite();
        $set->add(new CheckAgentMessage('isset'));
        $set->setOperator('AND');
        $trigger->terms->addTerm($set);

        if ($newEmailTemplates) {
            $trigger->actions->addAction(
                new SendUserNewEmail(
                    [
                        'template'    => 'SendmailBundle:emails_user:ticket_reply_by_agent.html.twig',
                        'do_cc_users' => true,
                        'from_name'   => 'performer',
                    ]
                )
            );
        } else {
            $trigger->actions->addAction(new SendUserEmail([
                'template'    => 'DeskPRO:emails_user:ticket-reply-byagent.html.twig',
                'do_cc_users' => true,
                'from_name'   => 'performer',
            ]));
        }

        if (!isset($ignore[$trigger->getSysName()])) {
            $this->getEm()->persist($trigger);
        }

        //-----
        // newreply: when agent replies via email, assign them if they havent set
        //-----

        $trigger = new TicketTrigger();
        $trigger->setEventTrigger('newreply');
        $trigger->setRunOrder(1000);
        $trigger->setByAgentMode(['email']);
        $trigger->setIsEnabled(true);
        $trigger->setSysName('default_newreply_agent_assignself');
        $trigger->setTitle('Assign self when replying by email');

        $set = new TriggerTermComposite();
        $set->add(new CheckAgent('nottouched'));
        $set->add(new CheckAgent('is', ['agent_ids' => [0]]));
        $set->setOperator('AND');
        $trigger->terms->addTerm($set);

        $trigger->actions->addAction(new SetAgent(['agent_id' => -1]));

        if (!isset($ignore[$trigger->getSysName()])) {
            $this->getEm()->persist($trigger);
        }

        $this->getEm()->flush();

        //-----
        // update: when user adds participants
        //-----

        $trigger = new TicketTrigger();
        $trigger->setEventTrigger(ExecutorContext::EVENT_UPDATE);
        $trigger->setRunOrder(1000);
        $trigger->setByUserMode(['email', 'form', 'portal', 'widget']);
        $trigger->setByAgentMode([]);
        $trigger->setIsEnabled(true);
        $trigger->setSysName('default_added_cc');
        $trigger->setTitle('Send email notification to added CC user');

        $set = new TriggerTermComposite();
        $set->add(new CheckUserMessage('not_isset'));
        $set->add(new CheckEmailCcAdded('is', ['ccs_added' => true]));
        $set->setOperator('AND');
        $trigger->terms->addTerm($set);

        $trigger->actions->addAction(new SendSpecificUserEmail([
            'emails'       => ['{{new_cc_emails}}'],
            'template'     => 'DeskPRO:emails_user:ticket-add-cc.html.twig',
            'from_name'    => 'helpdesk_name',
            'from_account' => 0,
            'headers'      => [],
        ]));

        if (!isset($ignore[$trigger->getSysName()])) {
            $this->getEm()->persist($trigger);
        }

        $this->getEm()->flush();
    }
}
