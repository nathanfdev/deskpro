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
		$this->rememberLastPage();

		$all_triggers = App::getEntityRepository('DeskPRO:TicketTrigger')->getEventTriggers(false, false);
		$all_escalations = App::getEntityRepository('DeskPRO:TicketTrigger')->getTimeTriggers(false, false);

		$urgency_options = UrgencyOptions::newFromSystemTriggers();
		$urgency_form = $this->get('form.factory')->create(new TicketUrgencyOptionsType(), $urgency_options);

		$autoclose_options = AutoCloseOptions::newFromSystemTriggers();
		$autoclose_form = $this->get('form.factory')->create(new TicketAutoCloseOptionsType(), $autoclose_options);

		$autoclose_page = $this->forward('AdminBundle:TicketAutoClose:list')->getContent();

		return $this->render('AdminBundle:TicketTriggers:list.html.twig', array(
			'all_triggers' => $all_triggers,
			'all_escalations' => $all_escalations,
			'urgency_options' => $urgency_options,
			'urgency_options_form' => $urgency_form->createView(),
			'autoclose_options' => $autoclose_options,
			'autoclose_options_form' => $autoclose_form->createView(),
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
			$trigger = App::getEntityRepository('DeskPRO:TicketTrigger')->find($trigger_id);
			if ($this->in->getString('trigger.event_trigger_option')) {
				$trigger['event_trigger_option'] = $this->in->getString('trigger.event_trigger_option');
			}
		}

		$term_options = App::getApi('tickets.search')->getSearchOptions($this->person);

		$form = $this->get('form.factory')->create(new EditTicketTriggerType($trigger), $trigger);

		$is_edited = false;
		$row_html = false;
		if ($this->in->getBool('process')) {

			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				$is_edited = true;

				App::getOrm()->beginTransaction();

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
								$tpl = App::getEntityRepository('DeskPRO:Template')->getTemplateForStyle($template_name);
							}

							if (!$tpl) {
								$template_name = 'DeskPRO:triggers:email' . time() . \Orb\Util\Util::requestUniqueId() . '.html.twig';
								$tpl = new \Application\DeskPRO\Entity\Template();
								$tpl['path'] = $template_name;
							}

							$tpl['template'] = $template_code;

							$action['options']['custom_template_name'] = $template_name;

							App::getOrm()->persist($tpl);
						}

						unset($action['options']['custom_template_default']);
						unset($action['options']['custom_template']);
					}
				}

				$trigger['actions'] = $actions;

				App::getOrm()->persist($trigger);
				App::getOrm()->flush();

				App::getOrm()->commit();

				$row_html = $this->renderView('AdminBundle:TicketTriggers:list-row.html.twig', array('trigger' => $trigger));
			}
		}

		return $this->render('AdminBundle:TicketTriggers:edit.html.twig', array(
			'trigger' => $trigger,
			'form'      => $form->createView(),
			'is_edited' => $is_edited,
			'row_html'  => $row_html,
			'term_options' => $term_options,
		));
	}

	############################################################################
	# save-urgency-options
	############################################################################

	/**
	 * Called via ajax to save urgency options
	 */
	public function saveUrgencyOptionsAction()
	{
		$urgency_options = UrgencyOptions::newFromSystemTriggers();
		$urgency_form = $this->get('form.factory')->create(new TicketUrgencyOptionsType(), $urgency_options);

		if ($this->get('request')->getMethod() == 'POST') {
			$urgency_form->bindRequest($this->get('request'));

			if ($urgency_form->isValid()) {
				$urgency_options->save();
			}
		}

		return $this->createJsonResponse(array('success' => true));
	}

	############################################################################
	# save-autoclose-options
	############################################################################

	/**
	 * Called via ajax to save autoclose options
	 */
	public function saveAutoCloseOptionsAction()
	{
		$autoclose_options = AutoCloseOptions::newFromSystemTriggers();
		$autoclose_form = $this->get('form.factory')->create(new TicketAutoCloseOptionsType(), $autoclose_options);

		if ($this->get('request')->getMethod() == 'POST') {
			$autoclose_form->bindRequest($this->get('request'));

			if ($autoclose_form->isValid()) {
				$autoclose_options->save();
			}
		}

		return $this->createJsonResponse(array('success' => true));
	}
}
