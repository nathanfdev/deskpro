<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
