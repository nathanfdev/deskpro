<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Abdullah Kiser <kiser.bd@gmail.com>
 */

namespace Application\AgentBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class NewDeal extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        #------------------------------
		# User fields
		#------------------------------

		$user_builder = $builder->create('person', 'form', array('data_class' => 'Application\\AgentBundle\\Form\\Model\\NewDealPerson'));                
		$user_builder->add('id', 'hidden');
		$user_builder->add('name', 'text', array('required' => false));
		$user_builder->add('email_address', 'text', array('required' => false));
		//$user_builder->add('organization', 'text', array('required' => false));
		//$user_builder->add('organization_position', 'text', array('required' => false));

		$builder->add($user_builder);

                $org_builder = $builder->create('organizations', 'form', array('data_class' => 'Application\\AgentBundle\\Form\\Model\\NewDealOrganization'));
                $org_builder->add('id', 'hidden');
		$org_builder->add('name', 'text', array('required' => false));

                $builder->add($org_builder);

                #------------------------------
		# Deal fields
		#------------------------------

		$builder->add('title', 'text');
                $builder->add('deal_type', 'text');
		$builder->add('deal_stage', 'text');
		$builder->add('agent_id', 'text', array('required' => false));

                $builder->add('deal_currency', 'text');
		$builder->add('probability', 'text');
		$builder->add('deal_value', 'text', array('required' => false));
                $builder->add('visibility', 'text');

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
			'data_class' => 'Application\\AgentBundle\\Form\\Model\\NewDeal',
		);
	}

    public function getName()
    {
        return 'newdeal';
    }
}