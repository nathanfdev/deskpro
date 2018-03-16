<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

namespace Application\DeskPRO\CustomFields\Form\Type;

use Application\DeskPRO\CustomFields\Form\AliasType;
use Application\DeskPRO\CustomFields\Form\StringObject;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CustomFieldTypeAbstract.
 */
abstract class CustomFieldTypeAbstract extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //------------------------------
        // Basic fields
        //------------------------------

        $builder->add('title', 'text', ['required' => true]);
        // use own string type instead of symfony's text type to avoid automatic conversion of empty string or null to null
        // this means we get to treat the cases where alias is not set and is set to empty { alias: "" }
        $builder->add('alias', AliasType::class, [
            'required'               => false,
            'data_class'             => StringObject::class,
            'null_handling_strategy' => 'null',
            'constraints'            => [
                new AppAssert\ObjectAlias([
                    'owner' => $builder->getData()->getField(),
                ]),
            ],
        ]);
        $builder->add('description', 'textarea', ['required' => false]);
        $builder->add('default_value', 'text', ['required' => false]);
        $builder->add('handler_class', 'hidden', ['required' => true]);
        $builder->add('validation_type', 'hidden', ['required' => false]);
        $builder->add('agent_validation_type', 'hidden', ['required' => false]);
        $builder->add('agent_validation_resolve', 'hidden', ['required' => false]);

        $builder->add('required', 'checkbox', ['required' => false]);
        $builder->add('custom_css_classname', 'text', ['required' => false]);

        $builder->add('is_enabled', 'checkbox', ['required' => false]);
        $builder->add('is_agent_field', 'checkbox', ['required' => false]);

        $this->buildCustomFieldForm($builder, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true,
        ]);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    protected function buildCustomFieldForm(FormBuilderInterface $builder, array $options)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'fielddef';
    }
}
