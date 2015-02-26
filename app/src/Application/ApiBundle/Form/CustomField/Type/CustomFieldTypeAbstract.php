<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 */

namespace Application\ApiBundle\Form\CustomField\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class CustomFieldTypeAbstract extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        #------------------------------
        # Basic fields
        #------------------------------

        $builder->add('title', 'text', array('required' => true));
        $builder->add('description', 'textarea', array('required' => false));
        $builder->add('default_value', 'text', array('required' => false));
        $builder->add('handler_class', 'hidden', array('required' => true));
        $builder->add('validation_type', 'hidden', array('required' => false));
        $builder->add('agent_validation_type', 'hidden', array('required' => false));
        $builder->add('agent_validation_resolve', 'hidden', array('required' => false));

        $builder->add('required', 'checkbox', array('required' => false));
        $builder->add('custom_css_classname', 'text', array('required' => false));

        $builder->add('is_enabled', 'checkbox', array('required' => false));
        $builder->add('is_agent_field', 'checkbox', array('required' => false));

        $this->buildCustomFieldForm($builder, $options);
    }

    protected function buildCustomFieldForm(FormBuilderInterface $builder, array $options)
    {
    }

    public function getName()
    {
        return 'fielddef';
    }
}
