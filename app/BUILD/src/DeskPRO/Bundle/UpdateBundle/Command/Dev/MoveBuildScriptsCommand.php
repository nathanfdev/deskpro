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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MoveBuildScriptsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dp:update:dev:move-build-scripts')
            ->setDescription('Moves build scripts "up" by re-stamping them from the time now. Useful after a merge and you need to rearrange scripts.')
            ->addArgument('ids', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'The IDs/names of the build scripts. Separate each by a space.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $buildIdsRaw = explode(',', trim($input->getOption('move-build-scripts', ''), ','));
        $buildIds    = [];

        foreach ($buildIdsRaw as $bid) {
            $b    = preg_replace('/[^0-9]/', '', $bid);
            $file = $this->getFilePath($b);
            if (!$b || !$file) {
                $output->writeln("<error>Invalid build script: $bid</error>");

                return 1;
            }
            if (!is_file($file)) {
                $output->writeln("<error>Invalid build script: $bid -- No file: $file</error>");

                return 1;
            }

            $buildIds[] = $b;
        }

        if (!$buildIds) {
            $output->writeln('<error>No builds specified</error>');

            return 1;
        }

        sort($buildIds, SORT_NUMERIC);

        $start = time();
        foreach ($buildIds as $bid) {
            ++$start;
            $new_bid = $start;

            $file     = $this->getFilePath($bid);
            $new_file = $this->getFilePath($new_bid);

            $output->writeln("<info>$bid -> $new_bid</info>");

            if (!is_dir(dirname($new_file))) {
                mkdir(dirname($new_file));
            }

            rename($file, $new_file);
            $output->writeln("\tOld Path: $file");
            $output->writeln("\tNew Path: $new_file");
            $output->writeln('');

            $f = file_get_contents($new_file);
            $f = str_replace('Build'.$bid, 'Build'.$new_bid, $f);
            file_put_contents($new_file, $f);
        }

        $output->write('Writing build-manifest.php ...');

        $reader       = $this->getContainer()->get('dp.build_tasks.manifest_reader');
        $gen          = $this->getContainer()->get('dp.build_tasks.manifest_gen');
        $manifestPath = $reader->getManifestPath();

        $file = $gen->getContents();
        file_put_contents($manifestPath, $file);

        $output->writeln(' done');
        $output->writeln('Done all');

        return 0;
    }

    /**
     * @param $v
     *
     * @return string
     */
    private function getFilePath($v)
    {
        $buildsRoot = DP_ROOT.'/src/Application/InstallBundle/Upgrade/Build';

        $y = @date('Y', $v);
        $m = @date('m', $v);

        return $buildsRoot."/$y/$m/Build$v.php";
    }
}
