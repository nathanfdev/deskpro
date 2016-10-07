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

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\NewSettings\SettingsBag;
use Application\DeskPRO\Translate\Translate;
use DeskPRO\Bundle\AppBundle\Validator\Constraints\DpPassword;
use DeskPRO\Bundle\PortalBundle\Form\Captcha\CaptchaDecider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class PersonChangePasswordType.
 */
class PersonChangePasswordType extends AbstractType
{
    /**
     * @var CaptchaDecider
     */
    private $captchaDecider;

    /**
     * @var Translate
     */
    private $translate;

    /**
     * Constructor.
     *
     * @param CaptchaDecider $captchaDecider
     * @param Translate      $translate
     */
    public function __construct(CaptchaDecider $captchaDecider, Translate $translate)
    {
        $this->captchaDecider = $captchaDecider;
        $this->translate      = $translate;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['require_current_password']) {
            $builder->add('current_password', PasswordType::class, [
                'required'    => true,
                'constraints' => [
                    new UserPassword([
                        'message' => 'portal.forms.error_password_current',
                    ]),
                ],
                'mapped' => false, // not mapping this, just using it for validation
            ]);
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $person = $event->getData();
            $form->add('new_password', RepeatedType::class, [
                'first_name'     => 'password',
                'first_options'  => ['label' => $this->phrase('portal.forms.label_password')],
                'second_name'    => 'confirm',
                'second_options' => ['label' => $this->phrase('portal.forms.label_password_confirm')],
                'type'           => PasswordType::class,
                'required'       => true,
                'constraints'    => [
                    new NotBlank(),
                    new DpPassword(['person' => $person]),
                ],
                'mapped' => false,
            ]);
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $event->getData()->setPassword($event->getForm()->get('new_password')->getData());
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'               => Person::class,
                'require_current_password' => true,
            ])
            ->setRequired('settings')
            ->setAllowedTypes('settings', SettingsBag::class)
        ;
    }

    /**
     * @param string $phrase
     * @param array  $vars
     *
     * @return string
     */
    public function phrase($phrase, $vars = [])
    {
        return $this->translate->phrase($phrase, $vars);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'person_change_password';
    }
}
