<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Email\EmailAccount\EditEmailAccount\Form\Type\IncomingAccount;

use Application\DeskPRO\Email\EmailAccount\IncomingAccount\ExchangeConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExchangeAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('host', 'text', ['required' => true]);
        $builder->add('port', 'text', ['required' => true]);
        $builder->add('user', 'text', ['required' => false]);
        $builder->add('password', 'dp_enc_password', ['required' => false]);
        $builder->add('mode', 'choice', [
            'required' => true,
            'choices'  => ['read' => 'read', 'delete' => 'delete', 'archive' => 'archive'],
        ]);
        $builder->add('read_mailbox', 'text', ['required' => false]);
        $builder->add('archive_mailbox', 'text', ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ExchangeConfig::class,
        ]);
    }

    public function getName()
    {
        return 'in_exchange_account';
    }
}
