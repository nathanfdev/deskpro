<?php

namespace Application\DeskPRO\CustomFields\Form\Type;

use Application\DeskPRO\CustomFields\Form\Model\DateField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Date;

/**
 * Class DateFieldType.
 */
class DateFieldType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
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
            'allow_extra_fields' => true,
            'data_class'         => DateField::class,
        ]);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
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
}
