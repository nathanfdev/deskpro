<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckBlobStorageCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('dp:check-blob-storage');
        $this->addOption('clean', null, InputOption::VALUE_NONE, 'DELETE records in blob_storage that have no parent blob record.');
        $this->setHelp('Checks for records in blob_storage that have no parent blob record.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('clean')) {
            $count = $this->getContainer()->getEm()->getRepository('DeskPRO:Blob')->cleanDanglingBlobStorageRows();
            $output->writeln("<info>$count records have been cleaned.</info>");
        } else {
            $count = $this->getContainer()->getEm()->getRepository('DeskPRO:Blob')->countDanglingBlobStorageRows();
            $output->writeln("<info>$count danling records exist. Use --clean option to delete them.</info>");
        }

        return 0;
    }
}
