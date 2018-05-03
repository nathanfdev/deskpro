<?php

namespace DeskPRO\Bundle\UpdateBundle\Command\Dev;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenBuildManifestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dp:update:dev:gen-build-manifest')
            ->setDescription('Re-generate the build manifest file.')
            ->addOption('output', 'o', InputOption::VALUE_NONE, 'Output the file to stdout instead of writing it')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $reader = $this->getContainer()->get('dp.build_tasks.manifest_reader');
        $gen    = $this->getContainer()->get('dp.build_tasks.manifest_gen');

        $file         = $gen->getContents();
        $manifestPath = $reader->getManifestPath();

        if ($input->getOption('output')) {
            echo $file;

            return 0;
        } else {
            if (file_put_contents($manifestPath, $file)) {
                echo "Wrote manifest file: $manifestPath\n";

                return 0;
            } else {
                echo "Failed to write manifest file: $manifestPath\n";

                return 1;
            }
        }
    }
}
