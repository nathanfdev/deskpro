<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Abdullah Kiser
 */

namespace Application\AgentBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class NewTask extends AbstractType
{

    public function buildForm(FormBuilder $builder, array $options)
    {
        #------------------------------
        # Basic fields
        #------------------------------

        $builder->add('title', 'text');
        $builder->add('date_due', 'datetime', array(
            'widget' => 'single_text',
            'empty_value' => '',
            'date_format'=>'M/d/y',
            'required' => false,
        ));

        $builder->add('visibility', 'choice', array(
            'choices' => array(0 => 'Public', 2 => 'Private'),
            'required' => true,
        ));
        $builder->add('assigned_agent_team', 'entity', array(
            'class' => 'Application\DeskPRO\Entity\AgentTeam',
            'property' => 'name',
            'required' => false,
            'empty_value'=> '--Agent Team--'
        ));

        $builder->add('assigned_agent', 'entity', array(
            'class' => 'Application\DeskPRO\Entity\Person',
			'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
				return $er->createQueryBuilder('p')
						->where('p.is_agent = true')
						->orderBy('p.name', 'ASC');
			},
            'property' => 'name',
            'required' => false,
            'empty_value'=> '--Agent--'
        ));
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Application\DeskPRO\Entity\Task',
        );
    }

    public function getName()
    {
        return 'newtask';
    }
}
