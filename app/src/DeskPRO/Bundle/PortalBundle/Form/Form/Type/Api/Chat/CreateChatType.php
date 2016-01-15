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
use DeskPRO\Bundle\AppBundle\Form\DataTransformer\TextStringTransformer;
use DeskPRO\Bundle\AppBundle\UserChat\UserChatSettings;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\Api\Chat\EventListener\AutoSetShouldSentTranscriptTrait;
use DeskPRO\Bundle\PortalBundle\Form\Form\Type\Api\Chat\EventListener\SetPersonListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CreateChatType.
 */
class CreateChatType extends AbstractType
{
    use AutoSetShouldSentTranscriptTrait;

    /**
     * @var SetPersonListener
     */
    private $set_person_listener;

    /**
     * @var UserChatSettings
     */
    private $user_chat_settings;

    /**
     * Constructor.
     *
     * @param SetPersonListener $set_person_listener
     * @param UserChatSettings  $user_chat_settings
     */
    public function __construct(SetPersonListener $set_person_listener, UserChatSettings $user_chat_settings)
    {
        $this->set_person_listener = $set_person_listener;
        $this->user_chat_settings  = $user_chat_settings;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'api_chat_create';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $email_constraints = [new Assert\Email()];
        if ($this->user_chat_settings->isPortalEmailValidation() && !$this->user_chat_settings->isPortalRequireLogin()) {
            $email_constraints[] = new Assert\NotBlank();
        }

        $builder
            ->add('name', 'text', [
                'property_path' => 'person_name',
                'required'      => false,
            ])
            ->add('email', 'email', [
                'property_path' => 'person_email',
                'required'      => false,
                'constraints'   => $email_constraints,
            ])
        ;

        $builder->get('name')->addModelTransformer(new TextStringTransformer());
        $builder->get('email')->addModelTransformer(new TextStringTransformer());

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onSetPersonEmailFromSession']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onCheckRequireLogin']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this->set_person_listener, 'onSetPerson']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onSetEmailValidationCode']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onSetShouldSentTranscript']);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection'               => false,
            'csrf_double_submit_protection' => false,
            'person'                        => null,
        ]);
    }

    /**
     * If session has user entity we can use assign it to the chat.
     * Uses if chat settings require user to be logged in.
     *
     * @param FormEvent $event
     */
    public function onSetPersonEmailFromSession(FormEvent $event)
    {
        $form   = $event->getForm();
        $person = $form->getConfig()->getOption('person');

        /** @var \Application\DeskPRO\Entity\Person $person */
        if ($person) {
            $event->setData(array_merge($event->getData(), [
                'email' => $person->getPrimaryEmailAddress(),
            ]));
        }
    }

    /**
     * If portal chat settings require email validation we need to generate a validation code.
     *
     * @param FormEvent $event
     */
    public function onSetEmailValidationCode(FormEvent $event)
    {
        // Option is disabled, skipping
        if (!$this->user_chat_settings->isPortalEmailValidation()) {
            return;
        }

        // Chat requires user to be logged in, skipping
        if ($this->user_chat_settings->isPortalRequireLogin()) {
            return;
        }

        /** @var ChatConversation $conversation */
        $conversation = $event->getData();
        $conversation->regenerateEmailValidationCode();
    }

    /**
     * @param FormEvent $event
     */
    public function onCheckRequireLogin(FormEvent $event)
    {
        $form   = $event->getForm();
        $person = $form->getConfig()->getOption('person');

        if ($this->user_chat_settings->isPortalRequireLogin() && !$person) {
            $form->addError(new FormError('Login required'));
        }
    }
}
