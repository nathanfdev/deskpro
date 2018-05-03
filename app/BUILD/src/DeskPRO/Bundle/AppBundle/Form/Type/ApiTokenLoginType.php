<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AuthenticationRequestType.
 */
class ApiTokenLoginType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', TextType::class, [
                'required'    => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('password', PasswordType::class, [
                'required'    => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
        ;

        $builder->addEventSubscriber(new ApiCaptchaListener());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'error_mapping'   => [
                'email'    => 'email',
                'password' => 'password',
            ],
        ]);
    }
}
