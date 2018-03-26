<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenTestEmailCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dp:email:gen-test-email');
        $this->addOption('from', 'm', InputOption::VALUE_REQUIRED, 'The address that the email sholud be sent from. This must be an existing outgoing account in DeskPRO.');
        $this->addOption('to', 't', InputOption::VALUE_REQUIRED, 'Who to send the email to');
        $this->addOption('as-pending', 'g', InputOption::VALUE_NONE, 'Insert the source as PENDING instead of ABORTED.');
        $this->setHelp('Generates a new email and adds it to the sendmail queue.');
    }

    /**
     * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    public function getContainer()
    {
        return parent::getContainer();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mailer = $this->getContainer()->getMailer();

        $date = date('D, jS M Y g:ia');
        $to   = $input->getOption('to');
        $from = $input->getOption('from');

        if (empty($to)) {
            $output->writeln('<error>You must specify the --to option');

            return 1;
        }

        if (empty($from)) {
            $email_accounts = $this->getContainer()->getEmailAccountManager();
            $account        = $email_accounts->getDefaultOutAccount();
            if ($account) {
                $from = $account->getUseEmailAddress();
            }
        }

        if (empty($from)) {
            $from = 'deskpro@'.php_uname('n');
        }

        $message = $mailer->createMessage();

        $tos = explode(',', $to);
        foreach ($tos as $to) {
            $to = trim($to);
            if ($to) {
                $message->addTo($to);
            }
        }
        $message->setFrom($from);
        $message->setSubject("Test Email - $date");
        $message->setBody("This is a test message sent at $date");
        $message->addPart("This is a test message sent at <strong>$date</strong>", 'text/html');

        if ($input->getOption('as-pending')) {
            $id = $mailer->queueMessage($message);
            $output->writeln("<info>Inserted email source: $id</info>\n");

            $output->writeln("The email was inserted with the 'pending' state and WILL BE SENT on the next queue run.");
        } else {
            $id = $mailer->insertMessage($message);

            $this->getContainer()->getDb()->update('sendmail_sources', [
                'status' => 'aborted',
            ], ['id' => $id]);

            $output->writeln("<info>Inserted email source: $id</info>\n");
            $output->writeln("The email was inserted with the 'aborted' state and will not be sent automatically.");
            $output->writeln('You may with to manually send this email using the following command:');
            $output->writeln("\tbin/console dp:email:sendsource $id\n");
        }

        return 0;
    }
}
