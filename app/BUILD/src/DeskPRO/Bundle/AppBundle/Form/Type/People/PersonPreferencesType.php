<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\People;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Form\Type\JsonArrayType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PersonPrefsType.
 */
class PersonPreferencesType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return JsonArrayType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'mapped' => false,
            ])
            ->setRequired('person')
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
        /** @var Person $person */
        $person = $form->getConfig()->getOption('person');

        if (is_array($form->getData())) {
            foreach ($form->getData() as $name => $value) {
                if ($value && !$person->hasPref($name)) {
                    $person->setPreference($name, $value);
                } elseif (!$value && $person->hasPref($name)) {
                    $person->setPreference($name, null);
                }
            }
        }
    }
}
