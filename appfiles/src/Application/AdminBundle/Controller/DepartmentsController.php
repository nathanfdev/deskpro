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

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Application\AdminBundle\Form\EditDepartmentForm;

/**
 * Handles creating/editing of API keys
 */
class DepartmentsController extends AbstractController
{
	############################################################################
	# list
	############################################################################

	/**
	 * Shows the main listing of departments
	 */
	public function listAction()
	{
		$all_departments = $this->em->createQuery("
			SELECT dep
			FROM DeskPRO:Department dep
			WHERE dep.parent IS NULL
			ORDER BY dep.title ASC
		")->getResult();

		return $this->render('AdminBundle:Departments:list.html.twig', array(
			'all_departments' => $all_departments
		));
	}



	############################################################################
	# edit
	############################################################################

	/**
	 * Edit a department
	 */
	public function editAction($department_id)
	{
		if (!$department_id) {
			$department = new Entity\Department();
		} else {
			$department = App::getEntityRepository('DeskPRO:Department')->find($department_id);
		}

		$form = EditDepartmentForm::create($this->get('form.context'), 'department', array('department' => $department));
		$form->bind($this->get('request'), $department);

		$is_edited = false;
		$row_html = false;
		if ($this->in->getBool('process')) {
			$is_edited = true;
			App::getOrm()->persist($department);
			App::getOrm()->flush();

			$row_html = $this->renderView('AdminBundle:Departments:list-row.html.twig', array('department' => $department));

			// Recreate form because parent_id field cant be changed, so we need to get rid of it
			$form = EditDepartmentForm::create($this->get('form.context'), 'department', array('department' => $department));
			$form->setData($department);
		}

		return $this->render('AdminBundle:Departments:edit.html.twig', array(
			'department' => $department,
			'form'      => $form,
			'is_edited' => $is_edited,
			'row_html'  => $row_html
		));
	}

	############################################################################
	# designer
	############################################################################

	/**
	 * "Designs" a department: sets custom ticket rules etc
	 */
	public function designerAction($department_id)
	{
		$department = App::getEntityRepository('DeskPRO:Department')->find($department_id);
		$custom_fields = App::getApi('custom_fields.tickets')->getFields();
		$term_options = App::getApi('tickets.search')->getSearchOptions($this->person);

		return $this->render('AdminBundle:Departments:edit.html.twig', array(
			'department' => $department,
			'custom_fields' => $custom_fields,
			'term_options' => $term_options
		));
	}
}