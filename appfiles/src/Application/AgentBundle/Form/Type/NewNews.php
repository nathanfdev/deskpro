<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AgentBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class NewNews extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
    {
		#------------------------------
		# Basic fields
		#------------------------------

		$builder->add('title', 'text');
		$builder->add('content', 'textarea');

		$builder->add('category_id', 'text');
		$builder->add('is_published', 'checkbox', array('required' => false));
		$builder->add('slug', 'text');

		$builder->add('labels', 'collection', array(
			'type' => 'text',
			'required' => false,
		));

		$builder->add('attach', 'collection', array(
			'type' => 'hidden',
			'required' => false,
			'allow_add' => true,
			'allow_delete' => true
		));
    }

	public function getDefaultOptions(array $options)
	{
		return array(
			'data_class' => 'Application\\AgentBundle\\Form\\Model\\NewNews',
		);
	}

    public function getName()
    {
        return 'newnews';
    }
}