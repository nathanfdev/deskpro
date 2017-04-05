<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
