<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Application\DeskPRO\Email\EmailAccount\EditEmailAccount\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EditEmailAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('address',         'email', array('required' => true));
        $builder->add('is_enabled',      'checkbox', array('required' => false));
        $builder->add('account_type',    'text',  array('required' => true));
        $builder->add('other_addresses', 'text',  array('required' => false));

        $builder->add('incoming_type', 'choice', array(
            'choices'  => array('gmail' => 'gmail', 'pop3' => 'pop3', 'imap' => 'imap', 'exchange' => 'exchange', 'noop' => 'noop'),
            'required' => true
        ));
        $builder->add('in_gmail_account',    new IncomingAccount\GmailAccountType());
        $builder->add('in_pop3_account',     new IncomingAccount\Pop3AccountType());
        $builder->add('in_imap_account',     new IncomingAccount\ImapAccountType());
        $builder->add('in_exchange_account', new IncomingAccount\ExchangeAccountType());

        $builder->add('outgoing_type', 'choice', array(
            'choices'  => array('gmail' => 'gmail', 'smtp' => 'smtp', 'php_mail' => 'php_mail'),
            'required' => true
        ));
        $builder->add('out_gmail_account', new OutgoingAccount\GmailAccountType());
        $builder->add('out_smtp_account',  new OutgoingAccount\SmtpAccountType());
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Application\\DeskPRO\\Email\\EmailAccount\\EditEmailAccount\\EditEmailAccount',
        ));
    }

    public function getName()
    {
        return 'email_account';
    }
}
