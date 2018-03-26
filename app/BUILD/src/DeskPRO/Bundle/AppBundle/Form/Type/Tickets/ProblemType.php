<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class ProblemType.
 */
class ProblemType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
            ])
            ->add('is_open', ApiBooleanType::class, [
                'required' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onSetOpenByDefault']);
    }

    /**
     * @param FormEvent $event
     */
    public function onSetOpenByDefault(FormEvent $event)
    {
        $data = $event->getData();
        if (is_array($data) && !array_key_exists('is_open', $data)) {
            $data['is_open'] = 1;
        }

        $event->setData($data);
    }
}
