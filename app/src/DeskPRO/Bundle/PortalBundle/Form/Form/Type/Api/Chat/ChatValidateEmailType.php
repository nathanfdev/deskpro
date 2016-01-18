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
namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type\Api\Chat;

use Application\DeskPRO\Entity\ChatConversation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class ChatValidateEmailType.
 */
class ChatValidateEmailType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'api_chat_validate_email';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('code', 'text', [
            'property_path' => 'email_validation_code',
        ]);

        $builder->get('code')->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onCheckCode']);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection'               => false,
            'csrf_double_submit_protection' => false,
        ]);
    }

    /**
     * @param FormEvent $event
     */
    public function onCheckCode(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        // Reset validation code from the request if no entity code
        if (!$form->getData()) {
            $event->setData(null);

            return;
        }

        /** @var ChatConversation $conversation */
        $conversation = $form->getParent()->getData();

        if ($conversation->getEmailValidated()) {
            $form->addError(new FormError('Email is already validated.'));
        } elseif (!$data) {
            $form->addError(new FormError('api.error_codes.required'));
        } elseif ($data !== $form->getData()) {
            $form->addError(new FormError('Wrong email validation code.'));
        } else {
            // Mark conversation email validated
            $conversation->setEmailValidated(true);
        }
    }
}
