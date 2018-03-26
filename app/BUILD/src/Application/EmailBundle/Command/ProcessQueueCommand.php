<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Command;

use Monolog;
use Symfony\Bridge\Monolog\Formatter\ConsoleFormatter;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessQueueCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dp:email:process-queue');
        $this->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Max number of emails to send in one go');
        $this->addOption('time', 'm', InputOption::VALUE_REQUIRED, 'Max time (seconds) before the process quits');
        $this->setHelp('Processes the email queue');
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
        $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);

        // Force console output
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

        $runner = $this->getContainer()->get('email.queue_runner');
        $runner->setLimits(
            intval($input->getOption('limit')) ?: 40,
            intval($input->getOption('time')) ?: 60
        );

        $count = $runner->run();

        $output->writeln(sprintf('<info>Sent %d messages</info>', $count));

        return 0;
    }
}
