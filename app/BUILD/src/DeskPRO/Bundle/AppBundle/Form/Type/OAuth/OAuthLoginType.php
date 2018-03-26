<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\OAuth;

use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class OAuthLoginType.
 */
class OAuthLoginType extends AbstractType
{
    /**
     * @var LanguageManager
     */
    private $languageManager;

    /**
     * Constructor.
     *
     * @param LanguageManager $languageManager
     */
    public function __construct(LanguageManager $languageManager)
    {
        $this->languageManager = $languageManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label'       => $this->languageManager->phrase('portal.forms.label_email'),
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(['strict' => true]),
                ],
                'attr' => [
                    'placeholder' => $this->languageManager->phrase('portal.forms.label_email'),
                ],
            ])
            ->add('password', PasswordType::class, [
                'label'       => $this->languageManager->phrase('portal.forms.label_password'),
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'attr' => [
                    'placeholder' => $this->languageManager->phrase('portal.forms.label_password'),
                ],
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return '';
    }
}
