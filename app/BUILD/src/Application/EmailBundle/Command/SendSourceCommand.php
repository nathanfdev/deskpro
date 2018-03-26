<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Command;

use Application\EmailBundle\Entity\SendmailSource;
use Monolog;
use Orb\Util\Strings;
use Symfony\Bridge\Monolog\Formatter\ConsoleFormatter;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SendSourceCommand extends ContainerAwareCommand
{
    const RETURN_NOT_FOUND      = 404;
    const RETURN_EXPECT_PENDING = 417;
    const RETURN_STATUS_PREVENT = 423;
    const RETURN_SEND_FAILURE   = 500;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dp:email:sendsource');
        $this->addOption('info', 'i', InputOption::VALUE_NONE, 'Do not actually process the source, just output info');
        $this->addOption('source', 'u', InputOption::VALUE_NONE, 'Output raw source. When used with --info, it will output info and the source at once.');
        $this->addOption('force', 'f', InputOption::VALUE_NONE, "Normally messages will only send if they are marked as 'pending' or 'retry'. Use --force if you want to send it even if it has some other status.");
        $this->addOption('expect-pending', 'g', InputOption::VALUE_NONE, 'Expect the status of the source to be PENDING. Use this when email messages are being queued and run from a queue server, and the queue server is executing this command.');
        $this->addArgument('id', InputArgument::REQUIRED, 'The record ID to send.');
        $this->setHelp('Attempts to send an stored email source');
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
        /** @var \Application\EmailBundle\Entity\SendmailSource $source */
        $source = $this->getContainer()->getEm()->find('EmailBundle:SendmailSource', $input->getArgument('id'));
        if (!$source) {
            $output->writeln('<error>Unknown SendmailSource ID</error>');

            return self::RETURN_NOT_FOUND;
        }

        //###############################################################################################################
        // Info
        //###############################################################################################################

        if ($input->getOption('info') || $input->getOption('source')) {
            if ($input->getOption('info')) {
                echo Strings::asciiTable([
                    ['ID', $source->getId()],
                    ['Ref', $source->getRef()],
                    ['Date', $source->getDateCreated()->format('Y-m-d H:i:s')],
                    ['Status', $source->getStatus()],
                    ['Is Sent?', $source->getDateSent() ? 'Yes :: '.$source->getDateSent()->format('Y-m-d H:i:s') : 'No'],
                    ['Next Attempt', $source->getDateNextAttempt() ? $source->getDateNextAttempt()->format('Y-m-d H:i:s') : 'never'],
                    ['Send Attempts', $source->getExecCount()],
                ]);
                echo "\n";

                if ($input->getOption('source')) {
                    echo "\n";
                    echo "SOURCE\n";
                    echo str_repeat('#', 72);
                    echo "\n";
                    echo "\n";
                }
            }

            if ($input->getOption('source')) {
                echo $this->getContainer()->getBlobStorage()->copyBlobRecordToString($source->getBlob());
            }

            if ($input->getOption('info') && $source->getLogBlob()) {
                echo "\n";
                echo "\n";
                echo "LOG\n";
                echo str_repeat('#', 72);
                echo "\n";
                echo "\n";
                echo $this->getContainer()->getBlobStorage()->copyBlobRecordToString($source->getLogBlob());
                echo "\n";
            }

            return 0;
        }

        //###############################################################################################################
        // Send
        //###############################################################################################################

        if ($input->getOption('expect-pending') && $source->getStatus() != SendmailSource::STATUS_PENDING) {
            $output->writeln(sprintf('<info>Source is marked as %s</info>', $source->getStatus()));
            $output->writeln('<error>Expected PENDING</error>.');
            $output->writeln('Aborting. Use --force if you want to send this email anyway.');

            return self::RETURN_EXPECT_PENDING;
        }

        if ($source->getStatus() != SendmailSource::STATUS_PENDING && $source->getStatus() != SendmailSource::STATUS_RETRY) {
            $output->writeln(sprintf('<info>Source is marked as %s</info>', $source->getStatus()));
            if (!$input->getOption('force')) {
                $output->writeln('Aborting. Use --force if you want to send this email anyway.');

                return self::RETURN_STATUS_PREVENT;
            }
        }

        $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);

        // Force console output on emails
        foreach ([
            'dp.email.out.queue',
            'dp.email.out.transport',
            'dp.email.out.mailer',
            'dp.email.out.raw_transport',
        ] as $n) {
            $console_handler = new ConsoleHandler($output);
            $console_handler->setFormatter(new ConsoleFormatter("%start_tag%[%datetime%] %channel%.%level_name%: %message%%end_tag%\n"));
            $this->getContainer()->get('monolog.logger.'.$n)->pushHandler($console_handler);
        }

        /** @var \Application\EmailBundle\Queue\QueueProc $proc */
        $proc = $this->getContainer()->get('email.queue_processor');
        $proc->process($source->toRecordArray());

        $this->getContainer()->getEm()->refresh($source);

        if ($source->getStatus() != SendmailSource::STATUS_COMPLETE) {
            return self::RETURN_SEND_FAILURE;
        }

        return 0;
    }
}
