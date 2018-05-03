<?php

namespace Application\DeskPRO\CustomFields\Form\Type;

use Application\DeskPRO\CustomFields\Form\Model\ChoiceField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ChoiceFieldType.
 */
class ChoiceFieldType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('field_type', 'choice', ['choices' => [
            'select'       => 'Select box (single selection)',
            'multi_select' => 'Mutli-Select box (multiple selection)',
            'radio'        => 'Radio buttons (single selection)',
            'checkbox'     => 'Checkboxes (multiple selection)',
        ]]);

        $builder->add('min_length', 'text', ['required' => false]);
        $builder->add('max_length', 'text', ['required' => false]);

        $builder->add('agent_min_length', 'text', ['required' => false]);
        $builder->add('agent_max_length', 'text', ['required' => false]);

        $builder->add('none_choice', CheckboxType::class, ['required' => false]);
        $builder->add('none_choice_title', TextType::class, ['required' => false]);

        $builder->get('default_value')->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            if (is_array($data)) {
                $event->setData(implode(',', $data));
            }
        });
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
            'data_class' => ChoiceField::class,
        ]);
    }
}
