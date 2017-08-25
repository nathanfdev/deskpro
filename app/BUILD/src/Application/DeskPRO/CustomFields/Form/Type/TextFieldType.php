<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace Application\DeskPRO\CustomFields\Form\Type;

use Application\DeskPRO\CustomFields\Form\Model\TextField;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class TextFieldType.
 */
class TextFieldType extends CustomFieldTypeAbstract
{
    /**
     * {@inheritdoc}
     */
    protected function buildCustomFieldForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('default_value', 'text', ['required' => false]);
        $builder->add('clickable_links', 'text', ['required' => false]);
        $builder->add('min_length', 'text', ['required' => false]);
        $builder->add('max_length', 'text', ['required' => false]);
        $builder->add('regex', 'text', ['required' => false]);
        $builder->add('regex_required', 'checkbox', ['required' => false]);

        $builder->add('agent_min_length', 'text', ['required' => false]);
        $builder->add('agent_max_length', 'text', ['required' => false]);
        $builder->add('agent_regex', 'text', ['required' => false]);
        $builder->add('agent_regex_required', 'checkbox', ['required' => false]);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(array $options)
    {
        return [
            'data_class' => TextField::class,
        ];
    }
}
