<?php

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\NewSettings\SettingsBag;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
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
     * @var LanguageManager
     */
    private $languageManager;

    /**
     * Constructor.
     *
     * @param CaptchaDecider  $captchaDecider
     * @param LanguageManager $languageManager
     */
    public function __construct(CaptchaDecider $captchaDecider, LanguageManager $languageManager)
    {
        $this->captchaDecider  = $captchaDecider;
        $this->languageManager = $languageManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['require_current_password']) {
            $builder->add('current_password', PasswordType::class, [
                'label'       => $this->phrase('portal.forms.label_current_password'),
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
        return $this->languageManager->phrase($phrase, $vars);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'person_change_password';
    }
}
