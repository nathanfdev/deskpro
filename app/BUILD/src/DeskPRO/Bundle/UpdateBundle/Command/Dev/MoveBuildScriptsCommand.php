<?php

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
        $buildIdsRaw = $input->getArgument('ids');
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
