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

use Application\DeskPRO\Translate\Translate;
use DeskPRO\Bundle\AppBundle\Validator\Constraints\DpPassword;
use DeskPRO\Bundle\PortalBundle\Form\Captcha\CaptchaDecider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\NotBlank;

class PersonChangePasswordType extends AbstractType
{
    /**
     * @var CaptchaDecider
     */
    private $captcha_decider;

    /**
     * @var Translate
     */
    private $translate;

    public function __construct(CaptchaDecider $captcha_decider, Translate $translate)
    {
        $this->captcha_decider = $captcha_decider;
        $this->translate       = $translate;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['require_current_password']) {
            $builder->add('current_password', 'password', array(
                'required'    => true,
                'constraints' => array(
                    new UserPassword(['message' => 'portal.forms.error_password_current']),
                ),
                'mapped' => false, // not mapping this, just using it for validation
            ));
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $person = $event->getData();
            $form->add('new_password', 'repeated', array(
                'first_name'     => 'password',
                'first_options'  => array('label' => $this->phrase('portal.forms.label_password')),
                'second_name'    => 'confirm',
                'second_options' => array('label' => $this->phrase('portal.forms.label_password_confirm')),
                'type'           => 'password',
                'required'       => true,
                'constraints'    => array(
                    new NotBlank(['message'  => 'portal.forms.error_required']),
                    new DpPassword(['person' => $person]),
                ),
                'mapped' => false,
            ));
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $event->getData()->setPassword($event->getForm()->get('new_password')->getData());
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'               => 'Application\DeskPRO\Entity\Person',
                'require_current_password' => true,
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

    public function phrase($phrase, $vars = [])
    {
        return $this->translate->phrase($phrase, $vars);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'person_change_password';
    }
}
