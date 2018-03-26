<?php

namespace Application\DeskPRO\CustomFields\Form;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AliasType extends AbstractType implements EventSubscriberInterface
{
    private $nullHandlingStrategy = 'string';

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->nullHandlingStrategy = $options['null_handling_strategy'];
        $builder->addEventSubscriber($this);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'compound'               => false,
            'null_handling_strategy' => 'string',
        ]);
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $alias = $event->getData();
        if ($this->nullHandlingStrategy === 'null' && is_null($alias)) {
            return;
        }

        if (is_array($alias)) {
            return;
        }

        $value = $alias ? (string) $alias : '';
        $event->setData(new StringObject($value));
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => 'onPreSubmit',
        ];
    }
}
