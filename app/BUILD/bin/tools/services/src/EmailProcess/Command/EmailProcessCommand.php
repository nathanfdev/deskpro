<?php

namespace DeskPRO\Services\EmailProcess\Command;

use DeskPRO\Component\TaskRunner\Reader\RedisReader;
use DeskPRO\Component\TaskRunner\Task\JsonTaskFactory;
use DeskPRO\Component\TaskRunner\TaskRunner;
use DeskPRO\Services\EmailProcess\TaskRunner\Processor\EmailTaskProcessor;
use Doctrine\Common\Util\Debug;
use DpRun\DpEnv;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EmailProcessCommand extends Command
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
            ->setName('email:process')
            ->setDescription('Starts the email processing routine')
            ->addOption('load-config', null, InputOption::VALUE_NONE, 'Fetch options from config.php if not defined as a CLI param')
            ->addOption('max-processes', null, InputOption::VALUE_REQUIRED, 'Specify the number of tasks to run in parallel')
            ->addOption('max-time', null, InputOption::VALUE_REQUIRED, 'Specify the max time this process should run before it exits gracefully. This will wait for all tasks to complete before exiting.')
            ->addOption('redis-key', null, InputOption::VALUE_REQUIRED, 'Specify the redis key (default to dp_incoming_email)')
            ->addOption('redis', null, InputOption::VALUE_REQUIRED, 'Specify the redis host. Example: tcp://127.0.0.1:6379')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Email processing starting ...');

        $stop_time    = $input->getOption('max-time') ?: 0;
        $task_timeout = 600;
        $max_tasks    = $input->getOption('max-processes') ?: 8;
        $redis_key    = $input->getOption('redis-key') ?: 'dp_incoming_email';

        if ($input->getOption('redis')) {
            $urlinfo = parse_url($input->getOption('redis'));
            $redis   = [
                'scheme'             => $urlinfo['scheme'],
                'host'               => $urlinfo['host'],
                'port'               => $urlinfo['port'],
                'read_write_timeout' => '20',
            ];
        } else {
            $redis = null;
        }

        if ($input->getOption('load-config')) {
            $config = $this->dpEnv->getConfig('async_email_processing');

            if (!$input->getOption('max-time') && isset($config['process']['max_time'])) {
                $stop_time = (int) $config['process']['max_time'];
            }
            if (!$input->getOption('max-processes') && isset($config['process']['max_processes'])) {
                $max_tasks = (int) $config['process']['max_processes'];
            }
            if (!$input->getOption('redis-key') && isset($config['process']['redis_key'])) {
                $redis_key = $config['process']['redis_key'];
            }
            if (!$input->getOption('redis') && isset($config['process']['redis_params'])) {
                $redis = $config['process']['redis_params'];
            }
        }

        if (!$redis) {
            $output->writeln('<error>No redis client params were set. Use --redis to specify the redis host.</error>');

            return 1;
        }

        $logger = new Logger('email.process');

        $formatter = new LineFormatter('[%datetime%] %channel%.%level_name%: %message%'."\n");

        if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
            $h = new ConsoleHandler($output);
            $h->setFormatter($formatter);
            $logger->pushHandler($h);

            $h = new StreamHandler(dp_get_log_dir().'/email.process.log', Logger::DEBUG);
            $h->setFormatter($formatter);
            $logger->pushHandler($h);
        } else {
            $h = new StreamHandler(dp_get_log_dir().'/email.process.log', Logger::INFO);
            $h->setFormatter($formatter);
            $logger->pushHandler($h);
        }

        $reader = new RedisReader([
            'task_factory' => new JsonTaskFactory(),
            'redis_params' => $redis,
            'redis_key'    => $redis_key,
            'logger'       => $logger,
        ]);

        $processor = new EmailTaskProcessor($logger);

        $options = [
            'task_timeout'    => $task_timeout,
            'max_tasks'       => $max_tasks,
            'stop_after_time' => $stop_time,
            'tick_time'       => 1.0,
            'reader'          => $reader,
            'processor'       => $processor,
            'logger'          => $logger,
        ];

        $runner = new TaskRunner($options);
        $output->writeln('Running ...');
        $runner->start();
        $output->writeln('Email processing ended');
    }
}
