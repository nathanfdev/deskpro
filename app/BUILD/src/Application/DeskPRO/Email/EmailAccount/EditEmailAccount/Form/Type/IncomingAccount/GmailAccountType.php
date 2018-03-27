<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Email\EmailAccount\EditEmailAccount\Form\Type\IncomingAccount;

use Application\DeskPRO\Email\EmailAccount\IncomingAccount\GmailConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GmailAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('user', 'email', ['required' => false]);
        $builder->add('password', 'dp_enc_password', ['required' => false]);
        $builder->add('type', 'choice', [
            'required'          => true,
            'choices'           => [GmailConfig::TYPE_POP3, GmailConfig::TYPE_OAUTH],
            'choices_as_values' => true,
        ]);
        $builder->add('token', 'text', ['required' => true]);
        $builder->add('refreshToken', 'text', ['required' => true]);
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
            'data_class' => GmailConfig::class,
        ]);
    }

    public function getName()
    {
        return 'in_gmail_account';
    }
}
