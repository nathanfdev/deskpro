<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Email\EmailAccount\EditEmailAccount\Form\Type;

use Application\DeskPRO\Email\EmailAccount\EditEmailAccount\EditEmailAccount;
use Application\DeskPRO\Entity\Brand;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditEmailAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('address', 'email', ['required' => true]);
        $builder->add('is_enabled', 'checkbox', ['required' => false]);
        $builder->add('account_type', 'text', ['required' => true]);
        $builder->add('is_all_brands', 'checkbox', ['required' => true]);
        $builder->add('brands', EntityType::class, [
            'class'        => Brand::class,
            'required'     => false,
            'expanded'     => true,
            'multiple'     => true,
            'choice_label' => 'name',
            'by_reference' => false,
        ]);
        $builder->add('other_addresses', 'text', ['required' => false]);

        $builder->add('incoming_type', 'choice', [
            'choices' => [
                'gmail'     => 'gmail',
                'pop3'      => 'pop3',
                'imap'      => 'imap',
                'exchange'  => 'exchange',
                'office365' => 'office365',
                'noop'      => 'noop',
            ],
            'required' => true,
        ]);
        $builder->add('in_gmail_account', IncomingAccount\GmailAccountType::class, ['allow_extra_fields' => true]);
        $builder->add('in_pop3_account', IncomingAccount\Pop3AccountType::class, ['allow_extra_fields' => true]);
        $builder->add('in_imap_account', IncomingAccount\ImapAccountType::class, ['allow_extra_fields' => true]);
        $builder->add('in_exchange_account', IncomingAccount\ExchangeAccountType::class, ['allow_extra_fields' => true]);
        $builder->add('in_office365_account', IncomingAccount\Office365AccountType::class, ['allow_extra_fields' => true]);

        $builder->add('outgoing_type', 'choice', [
            'choices' => [
                'gmail'     => 'gmail',
                'smtp'      => 'smtp',
                'php_mail'  => 'php_mail',
                'exchange'  => 'exchange',
                'office365' => 'office365',
            ],
            'required' => true,
        ]);
        $builder->add('out_gmail_account', OutgoingAccount\GmailAccountType::class, ['allow_extra_fields' => true]);
        $builder->add('out_smtp_account', OutgoingAccount\SmtpAccountType::class, ['allow_extra_fields' => true]);
        $builder->add('out_exchange_account', OutgoingAccount\ExchangeAccountType::class, ['allow_extra_fields' => true]);
        $builder->add('out_office365_account', OutgoingAccount\Office365AccountType::class, ['allow_extra_fields' => true]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => EditEmailAccount::class,
            'allow_extra_fields' => true,
        ]);
    }

    public function getName()
    {
        return 'email_account';
    }
}
