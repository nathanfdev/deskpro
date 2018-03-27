<?php

namespace Application\DeskPRO\CustomFields\Form\Type;

use Application\DeskPRO\CustomFields\Form\Model\TextField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TextFieldType.
 */
class TextFieldType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
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
    public function getParent()
    {
        return CustomFieldTypeAbstract::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TextField::class,
        ]);
    }
}
