<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Email\EmailAccount\EditEmailAccount\Form\Type\OutgoingAccount;

use Application\DeskPRO\Email\EmailAccount\OutgoingAccount\GmailConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GmailAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('user', 'email', ['required' => true]);
        $builder->add('password', 'dp_enc_password', ['required' => false]);
        $builder->add('token', 'text', ['required' => true]);
        $builder->add('refreshToken', 'text', ['required' => true]);
        $builder->add('type', 'choice', [
            'required'          => true,
            'choices'           => [GmailConfig::TYPE_PASSWORD, GmailConfig::TYPE_OAUTH],
            'choices_as_values' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GmailConfig::class,
        ]);
    }

    public function getName()
    {
        return 'out_gmail_account';
    }
}
