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

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type;

use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\Form\Form\FormFieldManager;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\AppBundle\Validator\Constraints\DpPassword;
use DeskPRO\Bundle\PortalBundle\Form\Captcha\CaptchaDecider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class PersonRegistrationType extends AbstractType
{
    /**
     * @var \DeskPRO\Bundle\AppBundle\Form\Form\FormFieldManager
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

    public function __construct(FormFieldManager $field_manager, LanguageManager $language_manager, CaptchaDecider $captcha_decider)
    {
        $this->field_manager    = $field_manager;
        $this->language_manager = $language_manager;
        $this->captcha_decider  = $captcha_decider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array(
            'label'       => $this->language_manager->phrase('portal.forms.label_name'),
            'required'    => true,
            'constraints' => array(
                new NotBlank(['message' => 'portal.forms.error_required']),
            ),
        ));

        $builder->add('primary_email', 'deskpro_person_email', array(
            'required' => true,
            'label'    => false,
        ));

        $builder->add('password', 'repeated', array(
            'first_name'    => 'password',
            'first_options' => array(
                'label' => $this->language_manager->phrase('portal.forms.label_password'),
            ),
            'second_name'    => 'confirm',
            'second_options' => array(
                'label' => $this->language_manager->phrase('portal.forms.label_password_confirm'),
            ),
            'type'        => 'password',
            'mapped'      => false,
            'required'    => true,
            'constraints' => array(
                new NotBlank(['message'  => 'portal.forms.error_required']),
                new DpPassword(['person' => new PersonGuest()]),
            ),
        ));

        $builder->add('timezone', 'timezone', array(
            'label' => $this->language_manager->phrase('portal.forms.label_timezone'),
            )
        );

        $field_manager   = $this->field_manager;
        $captcha_decider = $this->captcha_decider;
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($field_manager, $captcha_decider) {
            foreach ($field_manager->getAvailablePersonFields() as $field_def) {
                if (!$field_def->is_enabled) {
                    continue;
                }

                $id = $field_def->getId();
                $event->getForm()->add(
                    $id,
                    'deskpro_custom_data_person',
                    array(
                        'custom_data_field' => $field_def,
                        'person'            => $event->getData(),
                        'property_path'     => sprintf('getCustomDataCollection[%s]', $id),
                        'agent_interface'   => false,
                        'label'             => $field_def->getTitle(),
                    )
                );
            }

            if ($captcha_decider->shouldRequireRegistrationCaptchaForCurrentPerson()) {
                $event->getForm()->add('captcha', 'deskpro_captcha');
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($field_manager) {
            $event->getData()->setPassword($event->getForm()->get('password')->getData());
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Application\DeskPRO\Entity\Person',
            )
        );

        $resolver->setRequired(
            array('settings')
        );

        $resolver->setAllowedTypes(
            array(
                'settings' => 'Application\DeskPRO\NewSettings\SettingsBag',
            )
        );
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'person_registration';
    }
}
