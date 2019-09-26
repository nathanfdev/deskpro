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

        usort($logs, function ($a, $b) {
            $pattern = '#^\[(.*?)\].*#';

            preg_match($pattern, $a, $m1);
            preg_match($pattern, $b, $m2);

            $t1 = new \DateTime($m1[1]);
            $t2 = new \DateTime($m2[1]);

            return $t1 > $t2;
        });

        $outputFile = $this->getContainer()->get('deskpro.app_env')->getUserLogsDir().'/voice_cloud.log';
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
