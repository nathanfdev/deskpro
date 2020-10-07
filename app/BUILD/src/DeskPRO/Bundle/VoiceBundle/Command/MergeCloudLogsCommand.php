<?php

namespace DeskPRO\Bundle\VoiceBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MergeCloudLogsCommand.
 */
class MergeCloudLogsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dp:voice:merge-cloud-logs');
        $this->addArgument('path');
        $this->addOption('output', null, InputOption::VALUE_REQUIRED);
        $this->addOption('json', null, InputOption::VALUE_NONE);
        $this->addOption('task', null, InputOption::VALUE_REQUIRED);
        $this->addOption('chat', null, InputOption::VALUE_REQUIRED);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $finder = new \Symfony\Component\Finder\Finder();
        $finder->files()->in($input->getArgument('path'));

        $logs = [];
        foreach ($finder as $file) {
            $content  = $file->getContents();
            $fileLogs = explode("\n", $content);

            foreach ($fileLogs as $log) {
                if ($log) {
                    $logs[] = $log;
                }
            }
        }

        // filter by chat id
        if ($input->getOption('chat')) {
            $newChatLogs = array_filter($logs, function ($log) use ($input) {
                return strpos($log, 'chat_id = '.$input->getOption('chat'));
            });

            // filter by task id as well
            // in case if there are more than one task for this chat
            if ($input->getOption('task')) {
                $newChatTaskIds = [$input->getOption('task')];
            } else {
                $newChatTaskIds = array_map(function ($log) {
                    return preg_replace('/^.*task_id = (\d+)(,|").*$/', '$1', $log);
                }, $newChatLogs);
            }

            if ($newChatTaskIds) {
                $logs = array_filter($logs, function ($log) use ($newChatTaskIds) {
                    return preg_match('/task_id = ('.implode('|', $newChatTaskIds).')(,|")/', $log);
                });
            } else {
                $logs = [];
            }
        }

        usort($logs, function ($a, $b) use ($input) {
            if ($input->getOption('json')) {
                $a = json_decode($a, true);
                $b = json_decode($b, true);

                $t1 = new \DateTime($a['datetime']['date']);
                $t2 = new \DateTime($b['datetime']['date']);
            } else {
                $pattern = '#^\[(.*?)\].*#';

                preg_match($pattern, $a, $m1);
                preg_match($pattern, $b, $m2);

                $t1 = new \DateTime($m1[1]);
                $t2 = new \DateTime($m2[1]);
            }

            return $t1 > $t2;
        });

        if ($input->getOption('chat')) {
            $outputName = '/voice_cloud.chat_'.$input->getOption('chat');
            if ($input->getOption('task')) {
                $outputName .= '.task_'.$input->getOption('task');
            }

            $outputName .= '.log';
        } else {
            $outputName = '/voice_cloud.log';
        }

        $outputFile = $this->getContainer()->get('deskpro.app_env')->getUserLogsDir().$outputName;
        if ($input->getOption('output')) {
            $outputFile = $input->getOption('output');
        }

        if (!@file_put_contents($outputFile, implode("\n", $logs))) {
            $output->writeln("Unable to write into $outputFile");
        } else {
            $output->writeln("Logs are merged, the output file is $outputFile");
        }
    }
}
