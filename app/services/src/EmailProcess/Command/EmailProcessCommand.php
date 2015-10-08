<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DeskPRO\Services\EmailProcess\Command;

use DeskPRO\Component\TaskRunner\Reader\RedisReader;
use DeskPRO\Component\TaskRunner\Task\JsonTaskFactory;
use DeskPRO\Component\TaskRunner\TaskRunner;
use DeskPRO\Services\EmailProcess\TaskRunner\Processor\EmailTaskProcessor;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EmailProcessCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('email:process')
            ->setDescription('Starts the email processing routine')
            ->addOption('load-config', null, InputOption::VALUE_NONE, 'Specify this to use values from config.php')
            ->addOption('max-processes', null, InputOption::VALUE_REQUIRED, 'Specify the number of tasks to run in parallel')
            ->addOption('max-time', null, InputOption::VALUE_REQUIRED, 'Specify the max time this process should run before it exits gracefully. This will wait for all tasks to complete before exiting.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Email processing starting ...');

        $stop_time    = $input->getOption('max-time') ?: 0;
        $task_timeout = 600;
        $max_tasks    = $input->getOption('max-processes') ?: 8;

        $logger = new Logger('email.process');
        $logger->pushHandler(new StreamHandler(dp_get_log_dir().'/email.process.log', Logger::INFO));

        if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
            $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
            $logger->pushHandler(new ConsoleHandler($output));
        }

        $reader = new RedisReader(array(
            'task_factory' => new JsonTaskFactory(),
        ));

        $processor = new EmailTaskProcessor($logger);

        $options = array(
            'task_timeout'    => $task_timeout,
            'max_tasks'       => $max_tasks,
            'stop_after_time' => $stop_time,
            'tick_time'       => 5,
            'reader'          => $reader,
            'processor'       => $processor,
            'logger'          => $logger,
        );

        $runner = new TaskRunner($options);
        $output->writeln('Running ...');
        $runner->start();
        $output->writeln('Email collection ended');
    }
}
