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

namespace Application\AdminBundle\Form;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Arrays;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class EditDepartmentType extends AbstractType
{
	protected $is_new;

	public function __construct($is_new)
	{
		$this->is_new = $is_new;
	}

	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder->add('title', 'text');

		$builder->add('is_tickets_enabled', 'checkbox', array(
			'required' => false,
		));
		$builder->add('is_chat_enabled', 'checkbox', array(
			'required' => false,
		));

		if ($this->is_new) {
			$builder->add('parent', 'entity', array(
				'class' => 'DeskPRO:Department',
				'property' => 'title',
				'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
						return $er->createQueryBuilder('p')
								->where('p.parent IS NULL')
								->orderBy('p.display_order', 'ASC');
				},
				'empty_value' => '',
				'required' => false,
			));
		}
	}

	public function getName()
	{
		return 'department';
	}
}
