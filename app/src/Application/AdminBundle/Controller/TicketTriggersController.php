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

use Application\DeskPRO\Entity;
use Application\DeskPRO\App;
use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Util;

use Application\AdminBundle\Form\EditTicketTriggerType;
use Application\DeskPRO\UI\RuleBuilder;

use Application\AdminBundle\Urgency\UrgencyOptions;
use Application\AdminBundle\Form\TicketUrgencyOptionsType;
use Application\AdminBundle\AutoClose\AutoCloseOptions;
use Application\AdminBundle\Form\TicketAutoCloseOptionsType;

class TicketTriggersController extends AbstractController
{
	############################################################################
	# list
	############################################################################

	public function listAction()
	{
		$triggers = $this->em->getRepository('DeskPRO:TicketTrigger')->getGroupedTriggers();
		$triggers = Arrays::removeFalsey($triggers);

		return $this->render('AdminBundle:TicketTriggers:list.html.twig', array(
			'triggers' => $triggers,
		));
	}

	############################################################################
	# new-choose-type
	############################################################################

	public function newChooseTypeAction($trigger_type)
	{
		return $this->render('AdminBundle:TicketTriggers:edit-choosetype.html.twig', array(
			'trigger_type' => $trigger_type
		));
	}

	############################################################################
	# edit
	############################################################################

	public function editAction($trigger_id)
	{
		if (!$trigger_id) {
			$trigger = new Entity\TicketTrigger();
			$trigger['event_trigger'] = $this->in->getString('trigger.event_trigger');

			if ($this->in->getUint('event_trigger_time')) {
				$trigger->event_trigger_option = $this->in->getUint('event_trigger_time') . ' ' . $this->in->getString('event_trigger_scale');
			}

		} else {
			$trigger = $this->em->getRepository('DeskPRO:TicketTrigger')->find($trigger_id);
			if ($this->in->getString('trigger.event_trigger_option')) {
				$trigger['event_trigger_option'] = $this->in->getString('trigger.event_trigger_option');
			}
		}

		$ticket_options = App::getApi('tickets')->getTicketOptions($this->person);
		$ticket_options['people_term_options']  = array();
		$ticket_options['people_term_options']  = array();
		$ticket_options['people_term_options']['organizations']  = App::getOrm()->getRepository('DeskPRO:Organization')->getOrganizationNames();
		$ticket_options['people_term_options']['usergroups']  = App::getOrm()->getRepository('DeskPRO:Usergroup')->getUsergroupNames();
		$ticket_options['people_term_options']['languages']  = App::getOrm()->getRepository('DeskPRO:Language')->getTitles();

		$form = $this->get('form.factory')->create(new EditTicketTriggerType($trigger), $trigger);

		if ($this->in->getBool('process')) {

			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				if (!$trigger->title) {
					$trigger->title = '';
				}
				$this->em->beginTransaction();

				$term_rules = RuleBuilder::newTermsBuilder();
				$trigger['terms'] = $term_rules->readForm($this->in->getCleanValueArray('terms', 'raw' , 'discard'));

				$action_rules = RuleBuilder::newActionsBuilder();
				$trigger['actions'] = $action_rules->readForm($this->in->getCleanValueArray('actions', 'raw' , 'discard'));

				$actions = $trigger['actions'];
				foreach ($actions as &$action) {
					if (isset($action['options']['custom_template_default'])) {
						$template_name = $action['options']['custom_template_name'];
						$template_code = $action['options']['custom_template'];

						if ($template_code) {
							$tpl = null;
							if ($template_name) {
								$tpl = $this->em->getRepository('DeskPRO:Template')->getTemplateForStyle($template_name);
							}

							if (!$tpl) {
								$template_name = 'DeskPRO:triggers:email' . time() . \Orb\Util\Util::requestUniqueId() . '.html.twig';
								$tpl = new \Application\DeskPRO\Entity\Template();
								$tpl['path'] = $template_name;
							}

							$tpl['template'] = $template_code;

							$action['options']['custom_template_name'] = $template_name;

							$this->em->persist($tpl);
						}

						unset($action['options']['custom_template_default']);
						unset($action['options']['custom_template']);
					}
				}

				$trigger['actions'] = $actions;

				$this->em->persist($trigger);
				$this->em->flush();

				$this->em->commit();

				return $this->redirectRoute('admin_tickettriggers');
			}
		}

		$ticket_options['email_gateway_addresses'] = $this->em->getRepository('DeskPRO:EmailGatewayAddress')->getOptions();

		return $this->render('AdminBundle:TicketTriggers:edit.html.twig', array(
			'trigger' => $trigger,
			'event_trigger_time' => $trigger->getOptionTime(),
			'event_trigger_scale' => $trigger->getOptionScale(),
			'form'      => $form->createView(),
			'term_options' => $ticket_options,
		));
	}

	############################################################################
	# save-built-in
	############################################################################

	public function saveBuiltInAction()
	{
		$name = $this->in->getString('name');

		$trigger = $this->em->getRepository('DeskPRO:TicketTrigger')->findOneBy(array('sys_name' => $name));

		if (!$trigger) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		switch ($name) {
			case 'base_urgency':
				$actions = $trigger->actions;
				$actions[0]['options']['num'] = $this->in->getInt('num');
				$trigger->actions = $actions;
				break;

			default:
				$trigger->event_trigger_option = $this->in->getUint('time') . ' ' . $this->in->getString('scale');
				break;
		}

		$this->em->transactional(function($em) use ($trigger) {
			$em->persist($trigger);
			$em->flush();
		});

		return $this->createJsonResponse(array('success' => 1));
	}


	############################################################################
	# update-order
	############################################################################

	public function updateOrderAction()
	{
		$trigger_ids = $this->in->getCleanValueArray('trigger_ids', 'uint', 'discard');

		$x = 10;

		$this->db->beginTransaction();
		try {
			foreach ($trigger_ids as $id) {
				$this->db->update('ticket_triggers', array('run_order' => $x), array('id' => $id));
				$x += 10;
			}

			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		return $this->createJsonResponse(array('success' => true));
	}

	############################################################################
	# toggle-enabled
	############################################################################

	public function toggleEnabledAction()
	{
		$trigger = $this->em->find('DeskPRO:TicketTrigger', $this->in->getUint('trigger_id'));

		if ($trigger) {
			$trigger->is_enabled = $this->in->getBool('onoff');
			$this->em->persist($trigger);
			$this->em->flush();
		}

		return $this->createJsonResponse(array('success' => true));
	}
}
