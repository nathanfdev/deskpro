<?php

namespace DeskPRO\Services\EmailCollection\Command;

use DeskPRO\Component\TaskRunner\TaskRunner;
use DeskPRO\Services\EmailCollection\TaskRunner\Processor\AccountProcessor;
use DeskPRO\Services\EmailCollection\TaskRunner\Reader\AccountReader;
use DeskPRO\Services\Lib\Database;
use DpRun\DpEnv;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EmailCollectionCommand extends Command
{
    /**
     * @var DpEnv
     */
    private $dpEnv;

    public function setDpEnv(DpEnv $dpEnv)
    {
        $this->dpEnv = $dpEnv;
    }

    protected function configure()
    {
        $this
            ->setName('email:collection')
            ->setDescription('Starts the email collection routine')
            ->addOption('load-config', null, InputOption::VALUE_NONE, 'Fetch options from config.php if not defined as a CLI param')
            ->addOption('connect-interval', null, InputOption::VALUE_REQUIRED, 'How long to wait between each email account check (default 30s)')
            ->addOption('max-time', null, InputOption::VALUE_REQUIRED, 'Specify the max time this process should run before it exits gracefully. This will wait for all tasks to complete before exiting.')
            ->addOption('max-processes', null, InputOption::VALUE_REQUIRED, 'Specify the number of tasks to run in parallel')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Email collection starting ...');

        $stop_time    = (int) $input->getOption('max-time') ?: 0;
        $task_timeout = 600;
        $interval     = (int) $input->getOption('connect-interval') ?: 30;
        $max_tasks    = (int) $input->getOption('max-processes') ?: 999;

        if ($input->getOption('load-config')) {
            $config = $this->dpEnv->getConfig('async_email_processing');

            if (!$input->getOption('max-time') && isset($config['collect']['max_time'])) {
                $stop_time = (int) $config['collect']['max_time'];
            }
            if (!$input->getOption('max-processes') && isset($config['collect']['max_processes'])) {
                $max_tasks = (int) $config['collect']['max_processes'];
            }
            if (!$input->getOption('connect-interval') && isset($config['collect']['connect_interval'])) {
                $interval = (int) $config['collect']['connect_interval'];
            }
        }

        $logger = new Logger('email.collection');

        $formatter = new LineFormatter('[%datetime%] %channel%.%level_name%: %message%'."\n");

        if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
            $h = new ConsoleHandler($output);
            $h->setFormatter($formatter);
            $logger->pushHandler($h);

            $h = new StreamHandler(dp_get_log_dir().'/email.collection.log', Logger::DEBUG);
            $h->setFormatter($formatter);
            $logger->pushHandler($h);
        } else {
            $h = new StreamHandler(dp_get_log_dir().'/email.collection.log', Logger::INFO);
            $h->setFormatter($formatter);
            $logger->pushHandler($h);
        }

        $reader = new AccountReader($interval, function ($current) {
            return Database::getDbIfClosed($this->dpEnv->getConfig('database'), $current);
        });

        $processor = new AccountProcessor($logger);

        $options = [
            'task_timeout'    => $task_timeout,
            'max_tasks'       => $max_tasks,
            'stop_after_time' => $stop_time,
            'tick_time'       => 5.0,
            'reader'          => $reader,
            'processor'       => $processor,
            'logger'          => $logger,
        ];

        if ($stop_time) {
            //TODO - accounts staying marked as active when they shouldnt
            $db = Database::getDbIfClosed($this->dpEnv->getConfig('database'));
            $db->update('email_accounts', ['is_read_active' => 0], ['is_read_active' => 1]);
        }

        $runner = new TaskRunner($options);
        $output->writeln('Running ...');
        $runner->start();
        $output->writeln('Email collection ended');
    }
}
