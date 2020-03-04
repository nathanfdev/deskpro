<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\MassActions;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Form\Error\FormValidatorChecker;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MassActionCollectionType.
 */
class MassActionCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('ids', CollectionType::class, [
            'error_bubbling' => false,
            'entry_type'     => $options['params_class'],
            'entry_options'  => [
                'person'         => $options['person'],
                'error_bubbling' => false,
            ],
        ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit'], -1);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['params_class', 'person'])
            ->setAllowedTypes('params_class', 'string')
            ->setAllowedTypes('person', Person::class)
        ;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        foreach ($form->get('ids')->all() as $childForm) {
            // clear unmapped errors
            FormValidatorChecker::clearFormErrors($childForm, false);
        }
    }
}
