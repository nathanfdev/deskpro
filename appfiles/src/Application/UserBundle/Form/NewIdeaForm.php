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

use \Symfony\Component\Form;

/**
 * The new ticket form
 */
class NewIdeaForm extends \Symfony\Component\Form\Form
{
	protected $idea_properties = array();

	protected function configure()
	{
		$this->addOption('votes_remain');

		$this->add(new Form\TextField('title'));
		$this->add(new Form\TextAreaField('content'));

		$num = 3;
		if ($this->getOption('votes_remain')) {
			$num = min(3, $this->getOption('votes_remain'));
		}

		$this->add(new Form\ChoiceField('votes', array(
			'choices' => range(1, $num),
			'expanded' => true
		)));
		
		$this->add(new Form\ChoiceField('category_id', array(
			'choices' => App::getEntityRepository('DeskPRO:IdeaCategory')->getFullCategoryNames(' > ', false),
			'empty_value' => 'Choose a category',
			'required' => false // needed for empty_value to appear
		)));
	}
}