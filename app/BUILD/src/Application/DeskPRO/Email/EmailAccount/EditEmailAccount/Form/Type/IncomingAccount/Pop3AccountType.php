<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Email\EmailAccount\EditEmailAccount\Form\Type\IncomingAccount;

use Application\DeskPRO\Email\EmailAccount\IncomingAccount\Pop3Config;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Pop3AccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('user', 'text', ['required' => false]);
        $builder->add('password', 'dp_enc_password', ['required' => false]);
        $builder->add('host', 'text', ['required' => true]);
        $builder->add('port', 'text', ['required' => true]);
        $builder->add('secure_mode', 'choice', [
            'required'    => false,
            'choices'     => ['ssl' => 'ssl', 'tls' => 'tls'],
            'empty_value' => true,
        ]);
        $builder->add('disable_cert_validation', 'checkbox', ['required' => true]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Pop3Config::class,
        ]);
    }

    public function getName()
    {
        return 'in_pop3_account';
    }
}
