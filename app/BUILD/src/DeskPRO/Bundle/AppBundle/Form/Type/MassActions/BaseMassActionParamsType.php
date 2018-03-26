<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\MassActions;

use Application\DeskPRO\Entity\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BaseMassActionParamsType.
 */
class BaseMassActionParamsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (count($options['actions'])) {
            $builder->add('set_of_actions', ChoiceType::class, [
                'choices_as_values' => true,
                'multiple'          => true,
                'choices'           => $options['actions'],
                'mapped'            => false,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'error_bubbling' => true,
            ])
            ->setRequired(['actions', 'person'])
            ->setAllowedTypes('actions', 'array')
            ->setAllowedTypes('person', Person::class)
        ;
    }

    /**
     * @param FormEvent $event
     * @param string    $action
     *
     * @return bool
     */
    public static function hasAction(FormEvent $event, $action)
    {
        $form    = $event->getForm();
        $actions = $form->get('set_of_actions')->getData();

        return is_array($actions) && in_array($action, $actions);
    }
}
