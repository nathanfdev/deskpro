<?php

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\NewSettings\SettingsBag;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager;
use DeskPRO\Bundle\AppBundle\Form\Type\Captcha\DpCaptchaType;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomDataType;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonEmailType;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\AppBundle\Validator\Constraints\DpPassword;
use DeskPRO\Bundle\PortalBundle\Form\Captcha\CaptchaDecider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class PersonRegistrationType.
 */
class PersonRegistrationType extends AbstractType
{
    /**
     * @var \DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager
     */
    private $fieldManager;

    /**
     * @var LanguageManager
     */
    private $languageManager;

    /**
     * @var CaptchaDecider
     */
    private $captchaDecider;

    /**
     * Constructor.
     *
     * @param CustomFieldManager $fieldManager
     * @param LanguageManager    $languageManager
     * @param CaptchaDecider     $captchaDecider
     */
    public function __construct(CustomFieldManager $fieldManager, LanguageManager $languageManager, CaptchaDecider $captchaDecider)
    {
        $this->fieldManager    = $fieldManager;
        $this->languageManager = $languageManager;
        $this->captchaDecider  = $captchaDecider;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label'       => $this->languageManager->phrase('portal.forms.label_name'),
                'required'    => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('primary_email', PersonEmailType::class, [
                'label'    => $this->languageManager->phrase('portal.forms.label_email'),
                'required' => true,
            ])
            ->add('password', RepeatedType::class, [
                'first_name'    => 'password',
                'first_options' => [
                    'label' => $this->languageManager->phrase('portal.forms.label_password'),
                ],
                'second_name'    => 'confirm',
                'second_options' => [
                    'label' => $this->languageManager->phrase('portal.forms.label_password_confirm'),
                ],
                'type'        => PasswordType::class,
                'mapped'      => false,
                'required'    => true,
                'constraints' => [
                    new NotBlank(),
                    new DpPassword(['person' => new PersonGuest()]),
                ],
            ])
            ->add('timezone', TimezoneType::class, [
                'label' => $this->languageManager->phrase('portal.forms.label_timezone'),
            ])
        ;

        foreach ($this->fieldManager->getAvailablePersonDefs() as $def) {
            if (!$def->isEnabled()) {
                continue;
            }
            if ($def->isAgentField()) {
                continue;
            }

            $builder->add($def->getId(), CustomDataType::class, [
                'custom_def'      => $def,
                'property_path'   => 'custom_data',
                'agent_interface' => false,
            ]);
        }

        $builder->add('captcha', DpCaptchaType::class);
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $event->getData()->setPassword($event->getForm()->get('password')->getData());
        });
    }
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Person::class,
            ])
            ->setRequired('settings')
            ->setAllowedTypes('settings', SettingsBag::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'person_registration';
    }
}
