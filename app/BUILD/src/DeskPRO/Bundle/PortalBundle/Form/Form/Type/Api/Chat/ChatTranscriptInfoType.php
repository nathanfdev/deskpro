<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type\Api\Chat;

use DeskPRO\Bundle\AppBundle\Settings\WidgetSettingsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ChatTranscriptInfoType.
 */
class ChatTranscriptInfoType extends AbstractType
{
    /**
     * @var SetPersonListener
     */
    private $personListener;

    /**
     * @var WidgetSettingsResolver
     */
    private $chatSettings;

    /**
     * Constructor.
     *
     * @param SetPersonListener      $personListener
     * @param WidgetSettingsResolver $chatSettings
     */
    public function __construct(SetPersonListener $personListener, WidgetSettingsResolver $chatSettings)
    {
        $this->personListener = $personListener;
        $this->chatSettings   = $chatSettings;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'property_path' => 'person_name',
                'required'      => false,
            ])
            ->add('email', EmailType::class, [
                'property_path' => 'person_email',
                'constraints'   => [
                    new Assert\NotBlank(),
                    new Assert\Email(['strict' => true]),
                ],
            ])
        ;

        $builder->get('email')->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onCheckEmailValidation']);

        $builder->addEventSubscriber($this->personListener);
        $builder->addEventSubscriber(new AutoSetShouldSentTranscriptListener());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
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

        // Skip check to show just one not blank validation error because it's required field
        if (!$data) {
            return;
        }

        if ($data !== $form->getData()) {
            if ($this->chatSettings->isChatRequireLogin()) {
                $form->addError(new FormError('Unable to change email, chat require email is enabled.'));
            } elseif ($this->chatSettings->isChatEmailValidation()) {
                $form->addError(new FormError('Unable to change email, chat email validation is enabled.'));
            }
        }
    }
}
