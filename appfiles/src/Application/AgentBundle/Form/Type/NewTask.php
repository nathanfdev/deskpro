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
<<<<<<< HEAD
        $builder->add('date_due', 'date', array(
            'input'  => 'string',
=======
        $builder->add('date_due', 'datetime', array(            
>>>>>>> 2b2db2b... add agent team and individual agent list in the new task form
            'widget' => 'single_text',
            'empty_value' => '',
            'format'=>'y/m/d',
        ));
        

        $builder->add('is_completed', 'text');
        $builder->add('is_completed', 'choice', array(
            'choices' => array(0 => 'Public', 2 => 'Private'),
            'required' => true,
        ));
<<<<<<< HEAD
=======
        $builder->add('assigned_agent_team', 'entity', array(
            'class' => 'Application\DeskPRO\Entity\AgentTeam',
            'property' => 'name',
            'required' => false,
        ));
        $builder->add('assigned_agent', 'entity', array(
            'class' => 'Application\DeskPRO\Entity\Person',
            'property' => 'name',
            'required' => false,
        ));
>>>>>>> 2b2db2b... add agent team and individual agent list in the new task form
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