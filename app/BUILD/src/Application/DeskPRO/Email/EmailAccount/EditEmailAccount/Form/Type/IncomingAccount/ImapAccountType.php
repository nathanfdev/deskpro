<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Email\EmailAccount\EditEmailAccount\Form\Type\IncomingAccount;

use Application\DeskPRO\Email\EmailAccount\IncomingAccount\ImapConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImapAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('user', 'text', ['required' => false]);
        $builder->add('password', 'dp_enc_password', ['required' => false]);
        $builder->add('host', 'text', ['required' => true]);
        $builder->add('port', 'text', ['required' => true]);
        $builder->add('no_validation', 'checkbox', ['required' => true]);
        $builder->add('secure_mode', 'choice', [
            'required'    => false,
            'choices'     => ['ssl' => 'ssl', 'tls' => 'tls'],
            'empty_value' => true,
        ]);
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
            'data_class' => ImapConfig::class,
        ]);
    }

    public function getName()
    {
        return 'in_imap_account';
    }
}
