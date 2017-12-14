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

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\CustomFields\Form\Type;

use Application\DeskPRO\CustomFields\Form\Model\DateField;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Date;

class DateFieldType extends CustomFieldTypeAbstract
{
    protected function buildCustomFieldForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('default_value', 'text', ['required' => false, 'constraints' => [new Date()]]);
        $builder->add('default_mode', 'text', ['required' => true]);
        $builder->add('required', 'checkbox', ['required' => false]);
        $builder->add('agent_required', 'checkbox', ['required' => false]);

        $builder->add('date_valid_type', 'hidden');
        $builder->add('date_valid_date1', 'text', ['required' => false]);
        $builder->add('date_valid_date2', 'text', ['required' => false]);
        $builder->add('date_valid_range1', 'text', ['required' => false]);
        $builder->add('date_valid_range2', 'text', ['required' => false]);
        $builder->add('date_valid_dow', 'choice', [
            'multiple' => true,
            'expanded' => true,
            'required' => false,
            'choices'  => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
        ]);
        $builder->add('calendar', 'choice', [
            'required'          => false,
            'choices'           => ['gregorian', 'hijri'],
            'choices_as_values' => true,
        ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'preSubmit']);
    }

    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (isset($data['default_mode']) == 'date' && isset($data['default_value']) && $data['default_value']) {
            try {
                new \DateTime($data['default_value']);
            } catch (\Exception $e) {
                $data['default_value'] = ''; // reset invalid value
               $data['default_mode']   = '0'; // set mode to "no default"
               $event->setData($data);
            }
        }
    }

    public function getDefaultOptions(array $options)
    {
        return [
            'allow_extra_fields' => true,
            'data_class'         => DateField::class,
        ];
    }
}
