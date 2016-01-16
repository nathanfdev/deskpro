<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\Form\Type\WidgetSetup;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class WidgetChatSetupType.
 */
class WidgetChatSetupType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'widget_chat_setup';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('enabled', 'api_boolean')
            ->add('require_login', 'api_boolean')
            ->add('email_validation', 'api_boolean')
            ->add('request_user_info', 'api_boolean')
            ->add('proactive', 'api_boolean')
            ->add('begin_mode', 'choice', [
                'choices' => [
                    'conversation' => 'Conversation',
                    'form'         => 'Form',
                ],
            ])
            ->add('waiting_timeout', 'number')
            ->add('popup', new WidgetChatPopupSetupType())
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onRequireUserInfoEnabled']);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    /**
     * If email validation is on then request user info should be enabled.
     *
     * @param FormEvent $event
     */
    public function onRequireUserInfoEnabled(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        // If require login is enabled, skipping check, always simple
        if ($data['require_login']) {
            return;
        }

        // Email validation is disabled, skipping
        if (!$data['email_validation']) {
            return;
        }

        if (!$data['request_user_info']) {
            $form
                ->get('request_user_info')
                ->addError(new FormError('Email validation is enabled, request user info should be also enabled.'))
            ;
        }
    }
}
