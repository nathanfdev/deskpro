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

use \Application\DeskPRO\Entity;
use \Application\DeskPRO\App;
use \Orb\Util\Strings;
use \Orb\Util\Arrays;
use \Orb\Util\Util;

use \Symfony\Component\Form;
use \Application\AdminBundle\Form\EditTicketTriggerForm;

class TicketTriggersController extends AbstractController
{
	############################################################################
	# list
	############################################################################

	public function listAction()
	{
		$all_triggers = App::getOrm()->createQuery("
			SELECT t
			FROM DeskPRO:TicketTrigger t
			WHERE t.event_trigger NOT LIKE '%time_%'
			ORDER BY t.title ASC
		")->execute();

		$all_escalations = App::getOrm()->createQuery("
			SELECT t
			FROM DeskPRO:TicketTrigger t
			WHERE t.event_trigger LIKE '%time_%'
			ORDER BY t.title ASC
		")->execute();

		return $this->render('AdminBundle:TicketTriggers:list.html.twig', array(
			'all_triggers' => $all_triggers,
			'all_escalations' => $all_escalations,
		));
	}

	############################################################################
	# new-choose-type
	############################################################################

	public function newChooseTypeAction($trigger_type)
	{
		//$term_options = App::getApi('tickets.search')->getSearchOptions($this->person);
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
		} else {
			$trigger = App::getEntityRepository('DeskPRO:TicketTrigger')->find($trigger_id);
			if ($this->in->getString('trigger.event_trigger_option')) {
				$trigger['event_trigger_option'] = $this->in->getString('trigger.event_trigger_option');
			}
		}

		$term_options = App::getApi('tickets.search')->getSearchOptions($this->person);

		$form = EditTicketTriggerForm::create($this->get('form.context'), 'trigger', array('trigger' => $trigger));
		$form->bind($this->get('request'), $trigger);

		$is_edited = false;
		$row_html = false;
		if ($this->in->getBool('process')) {
			$is_edited = true;

			$trigger['terms'] = $this->in->getCleanValueArray('terms', 'raw' , 'discard');
			$trigger['actions'] = $this->in->getCleanValueArray('actions', 'raw' , 'discard');

			App::getOrm()->persist($trigger);
			App::getOrm()->flush();

			$row_html = $this->renderView('AdminBundle:TicketTriggers:list-row.html.twig', array('trigger' => $trigger));
		}

		return $this->render('AdminBundle:TicketTriggers:edit.html.twig', array(
			'trigger' => $trigger,
			'form'      => $form,
			'is_edited' => $is_edited,
			'row_html'  => $row_html,
			'term_options' => $term_options
		));
	}
}