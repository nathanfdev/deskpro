<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Email\EmailAccount\EditEmailAccount\Form\Type\OutgoingAccount;

use Application\DeskPRO\Email\EmailAccount\OutgoingAccount\ExchangeConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExchangeAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('user', 'text', ['required' => false]);
        $builder->add('password', 'dp_enc_password', ['required' => false]);
        $builder->add('host', 'text', ['required' => true]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ExchangeConfig::class,
        ]);
    }

    public function getName()
    {
        return 'out_exchange_account';
    }
}
