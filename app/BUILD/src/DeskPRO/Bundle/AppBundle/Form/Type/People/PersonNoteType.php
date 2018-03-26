<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\People;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonNote;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PersonNoteType.
 */
class PersonNoteType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('note', TextType::class, [
            'required' => true,
        ]);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit'], 100);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => PersonNote::class,
            ])
            ->setRequired(['agent', 'person'])
            ->setAllowedTypes('person', Person::class)
            ->setAllowedTypes('agent', Person::class)
        ;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $data   = $event->getData();
        $config = $event->getForm()->getConfig();

        if ($data instanceof PersonNote && !$data->getId()) {
            $data->setAgent($config->getOption('agent'));
            $data->setPerson($config->getOption('person'));
        }
    }
}
