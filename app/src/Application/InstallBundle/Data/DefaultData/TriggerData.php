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
 * @category Install
 */

namespace Application\InstallBundle\Data\DefaultData;

use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\Tickets\Actions\ModStopTriggers;
use Application\DeskPRO\Tickets\Actions\SendAgentEmail;
use Application\DeskPRO\Tickets\Actions\SendUserEmail;
use Application\DeskPRO\Tickets\Actions\SetAgent;
use Application\DeskPRO\Tickets\Actions\SetRequireValidation;
use Application\DeskPRO\Tickets\Actions\SetStatus;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckAgent;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckAgentTeam;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckUserIsNew;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckUserValidAgent;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckUserValidEmail;
use Application\DeskPRO\Tickets\Triggers\Terms\TriggerTermComposite;

class TriggerData extends AbstractDefaultData
{
	public function runInstall()
	{
		#-----
		# Send agent notifications
		#-----

		foreach (array(
			'newticket' => 'DeskPRO:emails_agent:ticket-new.html.twig',
			'newreply'  => 'DeskPRO:emails_agent:ticket-reply.html.twig',
			'update'    => 'DeskPRO:emails_agent:ticket-update.html.twig',
		) as $event_trigger => $template_name) {
			$trigger = new TicketTrigger();
			$trigger->event_trigger = $event_trigger;
			$trigger->by_user_mode = array('api', 'email', 'form', 'portal', 'widget');
			$trigger->by_agent_mode = array('api', 'email', 'web');
			$trigger->run_order = 1000;
			$trigger->sys_name = "default_{$event_trigger}_agentemail";
			$trigger->title = "Send agent notifications";
			$trigger->actions->addAction(new SendAgentEmail(array(
				'template'  => $template_name,
				'agent_ids' => array('notify_list'),
				'from_name' => 'performer',
			)));

			$this->getEm()->persist($trigger);
		}

		#-----
		# newticket: Send user new ticket by agent
		#-----

		$trigger = new TicketTrigger();
		$trigger->event_trigger = 'newticket';
		$trigger->run_order = 1000;
		$trigger->by_agent_mode = array('api', 'email', 'form', 'portal', 'widget');
		$trigger->is_enabled = false;
		$trigger->sys_name = 'default_newticket_byagent';
		$trigger->title = "Send user new ticket by agent";
		$trigger->actions->addAction(new SendUserEmail(array(
			'template'    => 'DeskPRO:emails_user:ticket-new-byagent.html.twig',
			'do_cc_users' => true,
			'from_name'   => 'performer',
		)));

		$this->getEm()->persist($trigger);

		#-----
		# newticket: Send user auto-reply
		#-----

		$trigger = new TicketTrigger();
		$trigger->event_trigger = 'newticket';
		$trigger->run_order = 1000;
		$trigger->by_user_mode = array('api', 'email', 'form', 'portal', 'widget');
		$trigger->is_enabled = false;
		$trigger->sys_name = 'default_newticket_userautoreply';
		$trigger->title = "Send auto-reply confirmation to user";
		$trigger->actions->addAction(new SendUserEmail(array(
			'template' => 'DeskPRO:emails_user:ticket-new-autoreply.html.twig',
			'do_cc_users' => true,
			'from_name' => 'helpdesk_name',
		)));

		$this->getEm()->persist($trigger);

		#-----
		# newreply: Send user auto-reply
		#-----

		$trigger = new TicketTrigger();
		$trigger->event_trigger = 'newreply';
		$trigger->run_order = 1000;
		$trigger->by_user_mode = array('api', 'email', 'form', 'portal', 'widget');
		$trigger->is_enabled = false;
		$trigger->sys_name = 'default_newreply_userautoreply';
		$trigger->title = "Send auto-reply confirmation to user";
		$trigger->actions->addAction(new SendUserEmail(array(
			'template' => 'DeskPRO:emails_user:ticket-reply-autoreply.html.twig',
			'do_cc_users' => true,
			'from_name' => 'helpdesk_name',
		)));

		$this->getEm()->persist($trigger);

		#-----
		# newreply: Send user new reply from agent
		#-----

		$trigger = new TicketTrigger();
		$trigger->event_trigger = 'newreply';
		$trigger->run_order = 1000;
		$trigger->by_agent_mode = array('api', 'email', 'web');
		$trigger->is_enabled = true;
		$trigger->sys_name = 'default_newreply_fromagent';
		$trigger->title = "Send user new reply from agent";
		$trigger->actions->addAction(new SendUserEmail(array(
			'template' => 'DeskPRO:emails_user:ticket-reply-byagent.html.twig',
			'do_cc_users' => true,
			'from_name' => 'performer',
		)));

		$this->getEm()->persist($trigger);

		#-----
		# newreply: when agent replies via email, assign them if they havent set
		#-----

		$trigger = new TicketTrigger();
		$trigger->event_trigger = 'newticket';
		$trigger->run_order = 1000;
		$trigger->by_agent_mode = array('email');
		$trigger->is_enabled = true;
		$trigger->sys_name = 'default_newreply_agent_assignself';
		$trigger->title = "Assign self when replying by email";

		$set = new TriggerTermComposite();
		$set->add(new CheckAgent('nottouched'));
		$set->add(new CheckAgent('is', array('agent_ids' => 0)));
		$set->add(new CheckAgentTeam('nottouched'));
		$set->add(new CheckAgentTeam('is', array('team_ids' => 0)));
		$set->setOperator('AND');
		$trigger->terms->addTerm($set);

		$trigger->actions->addAction(new SetAgent(array('agent_id' => -1)));

		$this->getEm()->persist($trigger);

		#-----
		# newticket: set require validation
		#-----

		foreach (array('email', 'form', 'portal', 'widget') as $mode) {
			$trigger = new TicketTrigger();
			$trigger->event_trigger = 'newticket';
			$trigger->run_order     = -1000;
			$trigger->by_user_mode  = array($mode);
			$trigger->is_enabled    = true;
			$trigger->is_hidden     = true;
			$trigger->sys_name      = 'default_newticket_requirevalid_' . $mode;
			$trigger->title         = 'Set validation';
			$trigger->terms->addTerm(new CheckUserIsNew('is'));
			$trigger->actions->addAction(new SetRequireValidation(array('require_validation' => true)));
			$this->getEm()->persist($trigger);
		}

		#-----
		# newticket: check validation
		#-----

		$trigger = new TicketTrigger();
		$trigger->event_trigger = 'newticket';
		$trigger->run_order     = -950;
		$trigger->by_user_mode  = array('api', 'email', 'form', 'portal', 'widget');
		$trigger->is_enabled    = true;
		$trigger->is_hidden     = true;
		$trigger->sys_name      = 'default_newticket_validemail';
		$trigger->title         = 'Check email validation';
		$trigger->terms->addTerm(new CheckUserValidEmail('not'));
		$trigger->actions->addAction(new SetStatus(array('status' => 'hidden.validating')));
		$trigger->actions->addAction(new SendUserEmail(array(
			'template' => 'DeskPRO:emails_user:ticket-new-validate-email.html.twig',
			'do_cc_users' => false,
			'from_name' => 'helpdesk_name',
		)));
		$trigger->actions->addAction(new ModStopTriggers());

		$this->getEm()->persist($trigger);

		#-----
		# newticket: check agent validation
		#-----

		$trigger = new TicketTrigger();
		$trigger->event_trigger = 'newticket';
		$trigger->run_order     = -900;
		$trigger->by_user_mode  = array('api', 'email', 'form', 'portal', 'widget');
		$trigger->is_enabled    = true;
		$trigger->is_hidden     = true;
		$trigger->sys_name      = 'default_newticket_validagent';
		$trigger->title         = 'Check agent validation';
		$trigger->terms->addTerm(new CheckUserValidAgent('not'));
		$trigger->actions->addAction(new SetStatus(array('status' => 'hidden.validating')));
		$trigger->actions->addAction(new ModStopTriggers());

		$this->getEm()->persist($trigger);

		$this->getEm()->flush();
	}

	public function runReset()
	{
		$this->getDb()->executeUpdate("DELETE FROM ticket_triggers WHERE sys_name IS NOT NULL");
		$this->runInstall();
	}
}