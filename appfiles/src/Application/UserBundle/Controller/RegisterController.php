<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\UserBundle\Controller;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Arrays;

use \Application\UserBundle\Form\RegPersonForm;

class RegisterController extends AbstractController
{
	public function finishAction()
	{
		$person = App::getEntityRepository('DeskPRO:Person')->find($this->session->get('finish_register_person'));

		// Invalid person if they dont exist or already are registered.
		// just pop the user back to index
		if (!$person OR $person['is_user']) {
			return $this->redirectRoute('user');
		}

		$people_field_defs = App::getApi('custom_fields.people')->getEnabledFields();
		$custom_fields_form = new \Symfony\Component\Form\CollectionField('custom_fields');
		$custom_fields = App::getApi('custom_fields.people')->getFieldsDisplayArray($people_field_defs, $person['custom_data'], $custom_fields_form);

		$reg_person = new \Application\UserBundle\RegPerson();
		$reg_person->setPerson($person);

		$reg_form = new RegPersonForm('register', array(
			'custom_fields' => $custom_fields
		));

		$reg_form->bind($this->request, $reg_person);

		if ($this->in->getBool('process')) {

			$this->session->remove('finish_register_person');

			$reg_form->save();

			if ($this->session->get('after_register')) {
				$url = $this->session->get('after_register');
				$this->session->remove('after_register');

				return $this->redirect($url);
			}

			return $this->redirectRoute('user');
		}

		return $this->render('UserBundle:Register:finish.html.twig', array(
			'person' => $person,
			'custom_fields' => $custom_fields,
			'form' => $reg_form,
		));
	}
}