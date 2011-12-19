<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\App;

/**
 * Handles creating/editing of Usersources
 */
class TestController extends AbstractController
{
	public function indexAction()
	{
		$em = $this->em;

		##BEGIN:create_trigger.urgency_base##
		$q = new \Application\DeskPRO\Entity\TicketTrigger();
		$q->title = 'urgency.base';
		$q->sys_name = 'urgency.base';
		$q->event_trigger = 'new_ticket';
		$q->is_enabled = 1;
		$q->terms = array();
		$q->actions = array(
			array(
				'type' => 'urgency_set',
				'options' => array(
					'num' => 1
				)
			)
		);

		$em->persist($q);
		$em->flush();

		##BEGIN:create_trigger.auto_close_close_user_reply##
		// When a ticket has been awaiting agent for 2 months, set it to resolved
		$q = new \Application\DeskPRO\Entity\TicketTrigger();
		$q->title = 'auto_close.close_user_reply';
		$q->sys_name = 'auto_close.close_user_reply';
		$q->event_trigger = 'time_user_waiting';
		$q->event_trigger_option = '5259487'; // 2 months
		$q->is_enabled = 1;
		$q->terms = array(
			array (
				'type' => 'status',
				'op' => 'is',
				'options' => array (
					'status' => 'awaiting_agent',
				),
			)
		);
		$q->actions = array(
			array(
				'type' => 'satus',
				'options' => array(
					'status' => 'resolved'
				)
			)
		);

		$em->persist($q);
		$em->flush();

		##BEGIN:create_trigger.auto_close_resolve_agent_reply##
		// When a ticket has been awaiting user for 5 days, set it to resolved
		$q = new \Application\DeskPRO\Entity\TicketTrigger();
		$q->title = 'auto_close.resolve_agent_reply';
		$q->sys_name = 'auto_close.resolve_agent_reply';
		$q->event_trigger = 'time_user_waiting';
		$q->event_trigger_option = '432000'; // 2 months
		$q->is_enabled = 1;
		$q->terms = array(
			array (
				'type' => 'status',
				'op' => 'is',
				'options' => array (
					'status' => 'awaiting_user',
				),
			)
		);
		$q->actions = array(
			array(
				'type' => 'satus',
				'options' => array(
					'status' => 'resolved'
				)
			)
		);

		$em->persist($q);
		$em->flush();

		##BEGIN:create_trigger.auto_close_close_user_reply##
		// When a ticket has been resolved for 15 months, set it to closed
		$q = new \Application\DeskPRO\Entity\TicketTrigger();
		$q->title = 'auto_close.close_resolved';
		$q->sys_name = 'auto_close.close_resolved';
		$q->event_trigger = 'date_resolved';
		$q->event_trigger_option = '1296000'; // 15 days
		$q->is_enabled = 1;
		$q->terms = array(
			array (
				'type' => 'status',
				'op' => 'is',
				'options' => array (
					'status' => 'resolved',
				),
			)
		);
		$q->actions = array(
			array(
				'type' => 'satus',
				'options' => array(
					'status' => 'closed'
				)
			)
		);

		$em->persist($q);
		$em->flush();

		return $this->render('AdminBundle:Test:index.html.twig');
	}
}
