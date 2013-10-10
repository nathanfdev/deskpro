<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

namespace Application\DeskPRO\TicketAccounts\Form\Type;

use Application\DeskPRO\Email\Form\Type\EmailTransportType;
use Application\DeskPRO\Email\IncomingAccount\Form\Type\GmailAccountType;
use Application\DeskPRO\Email\IncomingAccount\Form\Type\ImapAccountType;
use Application\DeskPRO\Email\IncomingAccount\Form\Type\Pop3AccountType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TicketAccountType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('department', 'entity', array(
			'class'         => 'DeskPRO:Department',
			'required'      => true,
			'query_builder' => function(EntityRepository $er) {
				return $er->createQueryBuilder('d')->where('d.is_tickets_enabled = true')->orderBy('d.display_order', 'ASC');
			}
		));

		$builder->add('email_address', 'text', array(
			'required'      => true,
		));
		$builder->add('connection_type', 'text', array(
			'required'      => true,
		));
		$builder->add('in_gmail_account', new GmailAccountType());
		$builder->add('in_pop3_account', new Pop3AccountType());
		$builder->add('in_imap_account', new ImapAccountType());
		$builder->add('email_transport', new EmailTransportType());
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Application\\DeskPRO\\TicketAccounts\\EditTicketAccount',
		));
	}

	public function getName()
	{
		return 'email_transport';
	}
}