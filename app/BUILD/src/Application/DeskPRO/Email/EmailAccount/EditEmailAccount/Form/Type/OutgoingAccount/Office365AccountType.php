<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Email\EmailAccount\EditEmailAccount\Form\Type\OutgoingAccount;

use Application\DeskPRO\Email\EmailAccount\OutgoingAccount\Office365Config;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Office365AccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('user', 'email', ['required' => true]);
        $builder->add('password', 'dp_enc_password', ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Office365Config::class,
        ]);
    }

    public function getName()
    {
        return 'out_office365_account';
    }
}
