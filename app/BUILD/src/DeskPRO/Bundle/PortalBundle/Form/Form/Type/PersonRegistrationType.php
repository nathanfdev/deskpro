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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\NewSettings\SettingsBag;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonEmailType;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\AppBundle\Validator\Constraints\DpPassword;
use DeskPRO\Bundle\PortalBundle\Form\Captcha\CaptchaDecider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class PersonRegistrationType.
 */
class PersonRegistrationType extends AbstractType
{
    /**
     * @var \DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager
     */
    private $field_manager;

    /**
     * @var LanguageManager
     */
    private $language_manager;

    /**
     * @var CaptchaDecider
     */
    private $captcha_decider;

    /**
     * Constructor.
     *
     * @param CustomFieldManager $field_manager
     * @param LanguageManager    $language_manager
     * @param CaptchaDecider     $captcha_decider
     */
    public function __construct(CustomFieldManager $field_manager, LanguageManager $language_manager, CaptchaDecider $captcha_decider)
    {
        $this->field_manager    = $field_manager;
        $this->language_manager = $language_manager;
        $this->captcha_decider  = $captcha_decider;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', [
                'label'       => $this->language_manager->phrase('portal.forms.label_name'),
                'required'    => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('primary_email', PersonEmailType::class, [
                'required' => true,
                'label'    => false,
            ])
            ->add('password', 'repeated', [
                'first_name'    => 'password',
                'first_options' => [
                    'label' => $this->language_manager->phrase('portal.forms.label_password'),
                ],
                'second_name'    => 'confirm',
                'second_options' => [
                    'label' => $this->language_manager->phrase('portal.forms.label_password_confirm'),
                ],
                'type'        => 'password',
                'mapped'      => false,
                'required'    => true,
                'constraints' => [
                    new NotBlank(),
                    new DpPassword(['person' => new PersonGuest()]),
                ],
            ])
            ->add('timezone', 'timezone', [
                'label' => $this->language_manager->phrase('portal.forms.label_timezone'),
            ])
        ;

        $field_manager   = $this->field_manager;
        $captcha_decider = $this->captcha_decider;
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($field_manager, $captcha_decider) {
            $form = $event->getForm();
            foreach ($field_manager->getAvailablePersonDefs() as $field_def) {
                if (!$field_def->isEnabled()) {
                    continue;
                }

                $id = $field_def->getId();
                $form->add($id, 'deskpro_custom_data', [
                    'custom_def'      => $field_def,
                    'property_path'   => 'custom_data',
                    'agent_interface' => false,
                    'label'           => $field_def->getTitle(),
                ]);
            }

            if ($captcha_decider->shouldRequireRegistrationCaptchaForCurrentPerson()) {
                $event->getForm()->add('captcha', 'deskpro_captcha');
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($field_manager) {
            $event->getData()->setPassword($event->getForm()->get('password')->getData());
        });
    }
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Person::class,
            ])
            ->setRequired(['settings'])
            ->setAllowedTypes([
                'settings' => SettingsBag::class,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'person_registration';
    }
}
