<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 *
 * @category Install
 */

namespace Application\InstallBundle\Data\DefaultData;

use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\Tickets\Actions\SendAgentNewEmail;
use Application\DeskPRO\Tickets\Actions\SendUserNewEmail;
use Application\DeskPRO\Tickets\Actions\SetAgent;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckAgent;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckAgentMessage;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckUserIsEmailed;
use Application\DeskPRO\Tickets\Triggers\Terms\TriggerTermComposite;

class TriggerData extends AbstractDefaultData
{
    public function runInstall()
    {
        $this->installTriggerRecords();
    }

    public function runReset()
    {
        $this->getDb()->executeUpdate('DELETE FROM ticket_triggers WHERE sys_name IS NOT NULL');
        $this->runInstall();
    }

    public function runSync()
    {
        $exist_names = $this->getDb()->fetchAllCol('SELECT sys_name FROM ticket_triggers WHERE sys_name IS NOT NULL');
        $this->installTriggerRecords($exist_names);
    }

    private function installTriggerRecords(array $ignore = [])
    {
        $ignore = array_fill_keys($ignore, true);

        //-----
        // Send agent notifications
        //-----

        foreach ([
            'newticket' => 'DeskPRO:emails_agent:ticket-new.html.twig',
            'newreply' => 'DeskPRO:emails_agent:ticket-reply.html.twig',
            'update' => 'DeskPRO:emails_agent:ticket-update.html.twig',
        ] as $event_trigger => $template_name) {
            $trigger                = new TicketTrigger();
            $trigger->event_trigger = $event_trigger;
            $trigger->by_user_mode  = ['api', 'email', 'form', 'portal', 'widget'];
            $trigger->by_agent_mode = ['api', 'email', 'web', 'mobile'];
            $trigger->run_order     = 1000;
            $trigger->sys_name      = "default_{$event_trigger}_agentemail";
            $trigger->title         = 'Send agent notifications';
            $trigger->actions->addAction(new SendAgentNewEmail([
                'template'  => $template_name,
                'agent_ids' => ['notify_list'],
                'from_name' => 'performer',
            ]));

            if (!isset($ignore[$trigger->sys_name])) {
                $this->getEm()->persist($trigger);
            }
        }

        //-----
        // newticket: Send user new ticket by agent
        //-----

        $trigger                = new TicketTrigger();
        $trigger->event_trigger = 'newticket';
        $trigger->run_order     = 1000;
        $trigger->by_agent_mode = ['api', 'email', 'web', 'mobile'];
        $trigger->is_enabled    = true;
        $trigger->sys_name      = 'default_newticket_byagent';
        $trigger->title         = 'Send user new ticket by agent';

        $set = new TriggerTermComposite();
        $set->add(new CheckAgentMessage('isset', ['message' => '']));
        $set->add(new CheckUserIsEmailed('not'));
        $set->setOperator('AND');
        $trigger->terms->addTerm($set);

        $trigger->actions->addAction(new SendUserNewEmail([
            'template'    => 'DeskPRO:emails_user:ticket-new-byagent.html.twig',
            'do_cc_users' => true,
            'from_name'   => 'performer',
        ]));

        if (!isset($ignore[$trigger->sys_name])) {
            $this->getEm()->persist($trigger);
        }

        //-----
        // newticket: Send user auto-reply
        //-----

        $trigger                = new TicketTrigger();
        $trigger->event_trigger = 'newticket';
        $trigger->run_order     = 1000;
        $trigger->by_user_mode  = ['api', 'email', 'form', 'portal', 'widget'];
        $trigger->is_enabled    = false;
        $trigger->sys_name      = 'default_newticket_userautoreply';
        $trigger->title         = 'Send auto-reply confirmation to user';

        $set = new TriggerTermComposite();
        $set->add(new CheckUserIsEmailed('not'));
        $set->setOperator('AND');
        $trigger->terms->addTerm($set);

        $trigger->actions->addAction(new SendUserNewEmail([
            'template'    => 'DeskPRO:emails_user:ticket-new-autoreply.html.twig',
            'do_cc_users' => true,
            'from_name'   => 'helpdesk_name',
        ]));

        if (!isset($ignore[$trigger->sys_name])) {
            $this->getEm()->persist($trigger);
        }

        //-----
        // newreply: Send user auto-reply
        //-----

        $trigger                = new TicketTrigger();
        $trigger->event_trigger = 'newreply';
        $trigger->run_order     = 1000;
        $trigger->by_user_mode  = ['api', 'email', 'form', 'portal', 'widget'];
        $trigger->is_enabled    = false;
        $trigger->sys_name      = 'default_newreply_userautoreply';
        $trigger->title         = 'Send auto-reply confirmation to user';

        $set = new TriggerTermComposite();
        $set->add(new CheckUserIsEmailed('not'));
        $set->setOperator('AND');
        $trigger->terms->addTerm($set);

        $trigger->actions->addAction(new SendUserNewEmail([
            'template'    => 'DeskPRO:emails_user:ticket-reply-autoreply.html.twig',
            'do_cc_users' => true,
            'from_name'   => 'helpdesk_name',
        ]));

        if (!isset($ignore[$trigger->sys_name])) {
            $this->getEm()->persist($trigger);
        }

        //-----
        // newreply: Send user new reply from agent
        //-----

        $trigger                = new TicketTrigger();
        $trigger->event_trigger = 'newreply';
        $trigger->run_order     = 1000;
        $trigger->by_agent_mode = ['api', 'email', 'web', 'mobile'];
        $trigger->is_enabled    = true;
        $trigger->sys_name      = 'default_newreply_fromagent';
        $trigger->title         = 'Send user new reply from agent';

        $set = new TriggerTermComposite();
        $set->add(new CheckAgentMessage('isset'));
        $set->setOperator('AND');
        $trigger->terms->addTerm($set);

        $trigger->actions->addAction(new SendUserNewEmail([
            'template'    => 'DeskPRO:emails_user:ticket-reply-byagent.html.twig',
            'do_cc_users' => true,
            'from_name'   => 'performer',
        ]));

        if (!isset($ignore[$trigger->sys_name])) {
            $this->getEm()->persist($trigger);
        }

        //-----
        // newreply: when agent replies via email, assign them if they havent set
        //-----

        $trigger                = new TicketTrigger();
        $trigger->event_trigger = 'newreply';
        $trigger->run_order     = 1000;
        $trigger->by_agent_mode = ['email'];
        $trigger->is_enabled    = true;
        $trigger->sys_name      = 'default_newreply_agent_assignself';
        $trigger->title         = 'Assign self when replying by email';

        $set = new TriggerTermComposite();
        $set->add(new CheckAgent('nottouched'));
        $set->add(new CheckAgent('is', ['agent_ids' => [0]]));
        $set->setOperator('AND');
        $trigger->terms->addTerm($set);

        $trigger->actions->addAction(new SetAgent(['agent_id' => -1]));

        if (!isset($ignore[$trigger->sys_name])) {
            $this->getEm()->persist($trigger);
        }

        $this->getEm()->flush();
    }
}
