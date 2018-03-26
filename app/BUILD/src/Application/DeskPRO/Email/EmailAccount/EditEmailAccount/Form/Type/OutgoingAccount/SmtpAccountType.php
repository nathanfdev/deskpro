<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Email\EmailAccount\EditEmailAccount\Form\Type\OutgoingAccount;

use Application\DeskPRO\Email\EmailAccount\OutgoingAccount\SmtpConfig;
use Application\DeskPRO\Encryption\Form\Type\DpEncPasswordType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SmtpAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', TextType::class, ['required' => false])
            ->add('password', DpEncPasswordType::class, ['required' => false])
            ->add('host', TextType::class, ['required' => true])
            ->add('port', TextType::class, ['required' => true])
            ->add(
                'secure_mode',
                ChoiceType::class,
                [
                    'required'    => false,
                    'choices'     => ['ssl' => 'ssl', 'tls' => 'tls'],
                    'empty_value' => true,
                ]
            )
            ->add('disable_cert_validation', CheckboxType::class, ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => SmtpConfig::class,
            ]
        );
    }

    public function getName()
    {
        return 'out_smtp_account';
    }
}
