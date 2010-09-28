<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage TechBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\TechBundle\Controller;

/**
 * Handles creating/editing of fields
 */
class FieldsController extends AbstractController
{
	############################################################################
	# /tech/fields                                             tech_admin_fields
	############################################################################

	public function indexAction()
	{

	}


	
	############################################################################
	# /tech/fields/:field_id/edit                    tech_admin_fields_editfield
	############################################################################

	public function editFieldAction($field_id)
	{
		if ($field_id) {
			$field = $this->getFieldOr404($field_id);
		} else {
			$field = $this->em->createEntity('CoreBundle:FormField');
			$field['typename'] = $this->in->getString('typename');
		}

		$renderer = new \Orb\Form\Renderer\Basic();
		$form = new \Application\TechBundle\Form\EditField(array(
			'name' => 'formfield',
			'renderer' => $renderer,
			'event_dispatcher' => $this['event_dispatcher'],
			'form_field' => $field
		));

		if ($this->isPostRequest()) {
			$form->setData($_POST);
			if ($form->isValid()) {
				$form->applyFormToEntity();
				$this->em->persist($field);
				$this->em->flush();
				echo "DONE";
			} else {
				// TODO proper handling
				print_r($form->getErrors());
			}
		}

		return $this->render('TechBundle:Fields:edit', array(
			'form' => $form
		));
	}



	############################################################################
	# /tech/fields/:field_id/test                    tech_admin_fields_testfield
	############################################################################

	public function testFieldAction()
	{
		// TODO show an example of what the field looks like rendered
	}



	############################################################################

	/**
	 * @return Application\CoreBundle\Entity\FormField
	 */
	protected function getFieldOr404($field_id)
	{
		try {
			$field = $this->em->find('CoreBundle:FormField', $field_id);
		} catch (\Doctrine\ORM\NoResultException $e) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no field with ID $field_id");
		}

		return $field;
	}
}