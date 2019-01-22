<?php

namespace Application\DeskPRO\CustomFields\Form\Type;

use Application\DeskPRO\CustomFields\Form\Model\DateTimeField;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Class DateTimeFieldType.
 */
class DateTimeFieldType extends DateFieldType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('calendar');
        $builder->remove('default_value');
        $builder->add('default_value', 'text', ['required' => false, 'constraints' => [new DateTime()]]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'preSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return DateFieldType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DateTimeField::class,
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
                $date                  = new \DateTime($data['default_value']);
                $data['default_value'] = $date->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                $data['default_value'] = ''; // reset invalid value
                $data['default_mode']  = '0'; // set mode to "no default"
            }
            $event->setData($data);
        }
    }
}
