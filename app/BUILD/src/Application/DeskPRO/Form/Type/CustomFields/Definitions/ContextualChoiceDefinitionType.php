<?php

namespace Application\DeskPRO\Form\Type\CustomFields\Definitions;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContextualChoiceDefinitionType extends ChoiceDefinitionType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        // if we need to define all properties, not only children
        if (!$options['children_only']) {
            $builder->get('options')
                ->add('allow_edit', 'checkbox');
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setRequired(['context']);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'cf_definition_contextual_choice';
    }
}
