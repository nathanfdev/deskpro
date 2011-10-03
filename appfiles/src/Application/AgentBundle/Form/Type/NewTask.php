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
        $builder->add('date_due', 'date', array(
            'input'  => 'datetime',
            'widget' => 'single_text',
            'format'=>'M/d/y',
        ));
        

        $builder->add('is_completed', 'text');
        $builder->add('is_completed', 'choice', array(
            'choices' => array(0 => 'Public', 2 => 'Private'),
            'required' => false,
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