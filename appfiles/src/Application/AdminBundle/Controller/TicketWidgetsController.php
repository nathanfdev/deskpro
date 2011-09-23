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
use Application\DeskPRO\Entity;

use Application\AdminBundle\Form\EditTicketPriorityType;

/**
 * Management of widgets
 */
class TicketWidgetsController extends AbstractController
{
	############################################################################
	# list
	############################################################################

	/**
	 * Shows the main listing of priorities
	 */
	public function listAction()
	{
		$all_widgets = App::getOrm()->createQuery("
			SELECT w
			FROM DeskPRO:Widget w
			WHERE w.section LIKE ?1 OR w.section LIKE ?2
		")->execute(array(1=> 'agent.ticket.%', 2=> 'ticket.%'));

		return $this->render('AdminBundle:TicketWidgets:list.html.twig', array(
			'all_widgets' => $all_widgets
		));
	}



	############################################################################
	# new-choose-type
	############################################################################

	public function newChooseTypeAction()
	{
		return $this->render('AdminBundle:TicketWidgets:edit-choosetype.html.twig', array(

		));
	}



	############################################################################
	# edit
	############################################################################

	/**
	 * Edit a priority
	 */
	public function editAction($widget_id)
	{
		if (!$widget_id) {
			$widget = new Entity\Widget();
		} else {
			$widget = App::getEntityRepository('DeskPRO:Widget')->find($widget_id);
		}

		$form = \Symfony\Component\Form\Form::create($this->get('form.context'), 'widget');

		$form->add(new \Symfony\Component\Form\HiddenField('name_id'));
		$form->add(new \Symfony\Component\Form\HiddenField('section'));
		$form->add(new \Symfony\Component\Form\HiddenField('template_name'));
		$form->add(new \Symfony\Component\Form\TextField('note'));

		$form_data = new \Symfony\Component\Form\Form('data');
		$form_data->add(new \Symfony\Component\Form\TextareaField('content'));

		$form->add($form_data);
		$form->bind($this->get('request'), $widget);

		$is_edited = false;
		$row_html = false;
		if ($this->in->getBool('process')) {
			$is_edited = true;
			App::getOrm()->persist($widget);
			App::getOrm()->flush();

			$row_html = $this->renderView('AdminBundle:TicketWidgets:list-row.html.twig', array('widget' => $widget));
		}

		return $this->render('AdminBundle:TicketWidgets:edit-content.html.twig', array(
			'widget'  => $widget,
			'form'      => $form,
			'is_edited' => $is_edited,
			'row_html'  => $row_html
		));
	}
}
