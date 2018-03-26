<?php

namespace Application\DeskPRO\Form\Type\CustomFields\Definitions;

use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SimpleDefinitionType extends AbstractType implements EventSubscriberInterface
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden')
            ->add('title', 'text', ['label' => false])
            ->add('display_order', 'hidden');
        $builder->addEventSubscriber($this);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => 'Application\DeskPRO\Entity\CustomFieldDefinition',
            ])
            ->setRequired([
                'context', 'parent',
            ])
            ->setAllowedTypes('context', [
                'Application\DeskPRO\Entity\Person',
                'Application\DeskPRO\Entity\Ticket',
                'Application\DeskPRO\Entity\Organization',
            ])
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'simple_definition';
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        // clean extra data
        if (!$data = $event->getData()) {
            return;
        }

        $data = array_intersect_key($data, $event->getForm()->all());
        if (isset($data['id']) && (int) $data['id'] < 1) {
            $data['id'] = null;
        }
        $event->setData($data);
    }

    /**
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        if (!$definition = $event->getForm()->getData()) {
            return;
        }

        if ($context = $event->getForm()->getConfig()->getOption('context')) {
            $definition['context_class'] = ClassUtils::getClass($context);
            $definition['context_id']    = $context['id'];
        }

        if ($parent = $event->getForm()->getConfig()->getOption('parent')) {
            $definition['owner_class'] = $parent['owner_class'];
            $definition['form_type']   = $parent['form_type'];
            $definition->parent        = $parent;
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT  => 'onPreSubmit',
            FormEvents::POST_SUBMIT => 'onPostSubmit',
        ];
    }
}
