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

class EditProductType extends AbstractType
{
	protected $is_new = false;
	public function __construct($is_new = false)
	{
		$this->is_new = $is_new;
	}

	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder->add('title', 'text');

		if ($this->is_new) {
			$builder->add('parent', 'entity', array(
				'class' => 'DeskPRO:Product',
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
		return 'product';
	}
}
