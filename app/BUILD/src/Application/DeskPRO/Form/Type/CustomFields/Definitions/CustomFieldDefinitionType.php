<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace Application\DeskPRO\Form\Type\CustomFields\Definitions;

use Application\DeskPRO\Entity\CustomFieldDefinition;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomFieldDefinitionType extends AbstractType implements EventSubscriberInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', [
                'required' => true,
            ])
            ->add('description', 'textarea', [
                'required' => false,
            ])
            ->add('is_enabled', 'checkbox', [
                'required' => true,
            ])
            ->add($builder->create('options', 'form')
                ->add('required', 'checkbox')
            );

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
                'allow_edit' => false,
            ])
            ->setDefined([
                'context',
                'allow_edit',
            ])
            ->setAllowedTypes('context', [
                'Application\DeskPRO\Entity\Person',
                'Application\DeskPRO\Entity\Ticket',
                'Application\DeskPRO\Entity\Organization',
            ])
        ;
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        // clean extra data
        if ($data = $event->getData()) {
            $data = array_intersect_key($data, $event->getForm()->all());
            $event->setData($data);
        }
    }

    /**
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        if (!$definition = $event->getForm()->getData()) {
            return;
        }

        if ($definition['form_type']) {
            return;
        }

        $definition['form_type'] = str_replace(['\Definitions', 'Definition'], ['', ''], get_class($this));
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

    public function getName()
    {
        return 'cf_definition';
    }

    /**
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['rendered_data'] = null;
        $view->vars['allow_edit']    = $options['allow_edit'];

        if (!($data = $form->getData()) instanceof CustomFieldDefinition) {
            return;
        }
        $view->vars['rendered_data'] = $data['title'];
    }
}
