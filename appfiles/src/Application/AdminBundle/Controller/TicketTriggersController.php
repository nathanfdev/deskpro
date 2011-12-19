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
		$sys_triggers = $this->em->getRepository('DeskPRO:TicketTrigger')->getSystemTriggers();

		return $this->render('AdminBundle:TicketTriggers:list.html.twig', array(
			'triggers' => $triggers,
			'sys_triggers' => $sys_triggers,
		));
	}

	############################################################################
	# new-choose-type
	############################################################################

	public function newChooseTypeAction($trigger_type)
	{
		$with_urgency = $this->in->getBool('with-urgency');

		if ($with_urgency) {
			if ($trigger_type == 'trigger') {
				return $this->redirectRoute('admin_tickettriggers_edit', array('trigger_id' => '0', 'with-urgency' => 1, 'trigger' => array('event_trigger' => 'property_change')));
			}
		}

		//$term_options = App::getApi('tickets.search')->getSearchOptions($this->person);
		return $this->render('AdminBundle:TicketTriggers:edit-choosetype.html.twig', array(
			'trigger_type' => $trigger_type,
			'with_urgency' => $with_urgency
		));
	}

	############################################################################
	# edit
	############################################################################

	public function editAction($trigger_id)
	{
		$with_urgency = $this->in->getBool('with-urgency');

		if (!$trigger_id) {
			$trigger = new Entity\TicketTrigger();
			$trigger['event_trigger'] = $this->in->getString('trigger.event_trigger');
			if ($this->in->getString('trigger.event_trigger_option')) {
				$trigger['event_trigger_option'] = $this->in->getString('trigger.event_trigger_option');
			}

			if ($with_urgency) {
				if (strpos($this->in->getString('trigger.event_trigger'), 'time_') === 0) {
					$trigger['actions'] = array(
						array('type' => 'urgency', 'options' => array('num' => 1))
					);
				} else {
					$trigger['terms'] = array(
						array('type' => 'urgency', 'op' => 'gte', 'options' => array('num' => 1))
					);
				}
			}

		} else {
			$trigger = $this->em->getRepository('DeskPRO:TicketTrigger')->find($trigger_id);
			if ($this->in->getString('trigger.event_trigger_option')) {
				$trigger['event_trigger_option'] = $this->in->getString('trigger.event_trigger_option');
			}
		}

		$ticket_options = App::getApi('tickets')->getTicketOptions($this->person);

		$form = $this->get('form.factory')->create(new EditTicketTriggerType($trigger), $trigger);

		if ($this->in->getBool('process')) {

			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
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

				return $this->redirectRoute('admin_tickettriggers_edit', array('trigger_id' => $trigger->id));
			}
		}

		return $this->render('AdminBundle:TicketTriggers:edit.html.twig', array(
			'trigger' => $trigger,
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
}
