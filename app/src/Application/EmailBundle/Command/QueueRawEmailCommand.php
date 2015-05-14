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

use Application\EmailBundle\Mail\RawMessage\RawMessageUtil;
use Application\EmailBundle\Mail\RawMessage\Rfc2822Decoder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class QueueRawEmailCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('dp:email:queue-raw-email');
        $this->addOption('as-pending', 'g', InputOption::VALUE_NONE, "Insert the source as PENDING instead of ABORTED.");
        $this->addArgument('file', InputArgument::OPTIONAL);
        $this->setHelp("Reads a raw email from a file inserts it into the system.");
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
        if ($input->getArgument('file')) {
            $path = realpath($input->getArgument('file'));

            if (!$path) {
                $output->writeln("<error>File does not exist</error>");
                return 1;
            }

            $raw_stream = fopen($path, 'r');
            if (!$raw_stream) {
                $output->writeln("<error>Could not open file</error>");
                return 1;
            }
        } else {
            $raw_stream = STDIN;
            if (!$raw_stream) {
                $output->writeln("<error>No source file provided</error>");
                return 1;
            }
        }

        $decoder = new Rfc2822Decoder();
        $raw = $decoder->createRawMessage($raw_stream);
        $message = RawMessageUtil::applyRawToSwift($raw);

        $mailer = $this->getContainer()->getMailer();

        if ($input->getOption('as-pending')) {
            $id = $mailer->queueMessage($message);
            $output->writeln("<info>Inserted email source: $id</info>\n");

            $output->writeln("The email was inserted with the 'pending' state and WILL BE SENT on the next queue run.");
        } else {
            $id = $mailer->insertMessage($message);

            $this->getContainer()->getDb()->update('sendmail_sources', array(
                'status' => 'aborted'
            ), array('id' => $id));

            $output->writeln("<info>Inserted email source: $id</info>\n");
            $output->writeln("The email was inserted with the 'aborted' state and will not be sent automatically.");
            $output->writeln("You may with to manually send this email using the following command:");
            $output->writeln("\tphp cmd.php dp:email:sendsource $id\n");
        }

        return 0;
    }
}
