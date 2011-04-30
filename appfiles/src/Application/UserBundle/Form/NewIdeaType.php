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
	protected $votes_remain = 0;

	public function __construct($votes_remain = 0)
	{
		$this->votes_remain = $votes_remain;
	}

	public function buildForm(FormBuilder $builder, array $options)
	{
		$num = 3;
		if ($this->votes_remain) {
			$num = min(3, $this->votes_remain);
		}
		
		$builder->add('title', 'text');
		$builder->add('content', 'textarea');

		$builder->add('votes', 'choice', array(
			'choices' => range(1, $num),
			'expanded' => 1,
		));

		$builder->add('category_id', 'choice', array(
			'choices' => App::getEntityRepository('DeskPRO:IdeaCategory')->getFullCategoryNames(' > ', false),
			'required' => false // needed for empty_value to appear
		));
	}

	public function getName()
	{
		return 'idea';
	}
}