<?php

/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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

namespace Application\EmailBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenTestEmailCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('dp:email:gen-test-email');
        $this->addOption('from', 'm', InputOption::VALUE_REQUIRED, "The address that the email sholud be sent from. This must be an existing outgoing account in DeskPRO.");
        $this->addOption('to', 't', InputOption::VALUE_REQUIRED, "Who to send the email to");
        $this->setHelp("Generates a new email and adds it to the sendmail queue.");
    }

    /**
     * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    public function getContainer()
    {
        return parent::getContainer();
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mailer = $this->getContainer()->getMailer();

        $date = date('D, jS M Y g:ia');
        $to = $input->getOption('to');
        $from = $input->getOption('from');

        if (empty($to)) {
            $output->writeln("<error>You must specify the --to option");
            return 1;
        }

        if (empty($from)) {
            $email_accounts = $this->getContainer()->getEmailAccountManager();
            $account = $email_accounts->getDefaultOutAccount();
            if ($account) {
                $from = $account->getUseEmailAddress();
            }
        }

        if (empty($from)) {
            $from = 'deskpro@' . php_uname('n');
        }

        $message = $mailer->createMessage();
        $message->setTo($to);
        $message->setFrom($from);
        $message->setSubject("Test Email - $date");
        $message->setBody("This is a test message sent at $date");
        $message->addPart("This is a test message sent at <strong>$date</strong>", 'text/html');

        $id = $mailer->insertMessage($message);

        $this->getContainer()->getDb()->update('sendmail_sources', array(
            'status' => 'aborted'
        ), array('id' => $id));

        $output->writeln("<info>Inserted email source: $id</info>\n");
        $output->writeln("The email is inserted with the 'aborted' state and will not be sent automatically.");
        $output->writeln("You may with to manually send this email using the following command:");
        $output->writeln("\tphp cmd.php dp:email:sendsource $id\n");

        return 0;
    }
}
