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
 * Class ChatTranscriptInfoType.
 */
class ChatTranscriptInfoType extends AbstractType
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
        return 'api_chat_transcription_info';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', [
                'property_path' => 'person_name',
                'required'      => false,
            ])
            ->add('email', 'email', [
                'property_path' => 'person_email',
                'constraints'   => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                ],
            ])
        ;

        $builder->get('name')->addModelTransformer(new TextStringTransformer());
        $builder->get('email')->addModelTransformer(new TextStringTransformer());
        $builder->get('email')->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onCheckEmailValidation']);

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this->set_person_listener, 'onSetPerson']);
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
        ]);
    }

    /**
     * Check that email was not changed if email validation is enabled.
     *
     * @param FormEvent $event
     */
    public function onCheckEmailValidation(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if ($data !== $form->getData()) {
            if ($this->user_chat_settings->isPortalRequireLogin()) {
                $form->addError(new FormError('Unable to change email, chat require email is enabled.'));
            } elseif ($this->user_chat_settings->isPortalEmailValidation()) {
                $form->addError(new FormError('Unable to change email, chat email validation is enabled.'));
            }
        }
    }
}
