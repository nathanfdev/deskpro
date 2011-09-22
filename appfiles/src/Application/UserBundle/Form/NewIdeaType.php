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

namespace Application\UserBundle\Form;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Arrays;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

/**
 * The new ticket form
 */
class NewIdeaType extends AbstractType
{
	/**
	 * The actual person (logged in)
	 */
	protected $person;

	/**
	 * A person object we'll use for things like permissions.
	 * So if the person is a guest, then this is a guest object
	 * with basic properties.
	 */
	protected $mock_person;

	public function __construct($person)
	{
		$this->person = $person;
	}

	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder->add('title', 'text');
		$builder->add('content', 'textarea');

		$builder->add('category_id', 'choice', array(
			'choices' => App::getEntityRepository('DeskPRO:IdeaCategory')->getFullCategoryNames(' > ', false),
			'required' => false // needed for empty_value to appear
		));

		$this->buildPersonForm($builder);
	}

	/**
	 * Configures the person form
	 */
	protected function buildPersonForm(FormBuilder $builder)
	{
		if ($this->person AND $this->person['id']) {
			$this->mock_person = $this->person;
		} else {
			$this->person = null;

			// We need this for some things to get basic permissions
			$this->mock_person = Entity\Person::newContactPerson();
		}

		$person_builder = $builder->create('person', 'form')
			->add('name', 'text', array('data' => $this->mock_person['name']));

		if (!$this->person) {
			$person_builder->add('email', 'text');
		}
		$builder->add($person_builder);
	}

	public function getName()
	{
		return 'idea';
	}
}
