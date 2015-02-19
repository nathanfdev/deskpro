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

use Symfony\Bridge\Monolog\Formatter\ConsoleFormatter;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Monolog;

class ProcessQueueCommand extends ContainerAwareCommand
{/**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('dp:email:process-queue');
        $this->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Max number of emails to send in one go');
        $this->addOption('time', 'm', InputOption::VALUE_REQUIRED, 'Max time (seconds) before the process quits');
        $this->setHelp("Processes the email queue");
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
        $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);

        // Force console output
        foreach (array(
             'dp.email.out.queue',
             'dp.email.out.transport',
             'dp.email.out.mailer',
             'dp.email.out.raw_transport'
         ) as $n) {
            $console_handler = new ConsoleHandler($output);
            $console_handler->setFormatter(new ConsoleFormatter("%start_tag%[%datetime%] %channel%.%level_name%: %message%%end_tag%\n"));
            $this->getContainer()->get('monolog.logger.'.$n)->pushHandler($console_handler);
        }

        $runner = $this->getContainer()->get('email.queue_runner');
        $runner->setLimits(
            intval($input->getOption('limit')) ?: 40,
            intval($input->getOption('time')) ?: 20
        );

        $count = $runner->run();

        $output->writeln(sprintf("<info>Sent %d messages</info>", $count));

        return 0;
    }
}
