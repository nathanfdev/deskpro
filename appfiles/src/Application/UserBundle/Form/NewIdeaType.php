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

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Arrays;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

/**
 * The new ticket form
 */
class NewIdeaType extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder->add('title', 'text');
		$builder->add('content', 'textarea');

		$builder->add('category_id', 'choice', array(
			'choices' => App::getEntityRepository('DeskPRO:IdeaCategory')->getFullCategoryNames(' > ', false),
			'required' => false // needed for empty_value to appear
		));

		$builder->add('person_name', 'text', array('required' => false));
		$builder->add('person_email', 'text', array('required' => false));
	}

	public function getName()
	{
		return 'idea';
	}
}
