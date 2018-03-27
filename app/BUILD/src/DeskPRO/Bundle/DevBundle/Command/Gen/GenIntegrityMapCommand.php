<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\DevBundle\Command\Gen;

use DeskPRO\Bundle\InstallBundle\FileIntegrity\FileHasher;
use DeskPRO\Bundle\InstallBundle\FileIntegrity\Generator\IntegrityMapGenerator;
use DeskPRO\Bundle\InstallBundle\FileIntegrity\ProjectFileSet;
use DeskPRO\Component\Util\MapUtils;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenIntegrityMapCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dpdev:gen:integrity-map');
        $this->addOption('clean-missing', null, InputOption::VALUE_NONE, 'Read the existing map and just remove files that dont exist');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        ini_set('memory_limit', -1);

        $writePath = $DP_ENV->getAppBaseKernelCacheDir().DIRECTORY_SEPARATOR.'integrity_file_map.dat';
        $set       = new ProjectFileSet($DP_ENV);

        if ($input->getOption('clean-missing') && file_exists($writePath)) {
            $map = json_decode(file_get_contents($writePath), true);

            $output->writeln('Cleaning map...');
            $startTime   = microtime(true);
            $countBefore = count($map);

            $map = MapUtils::filter($map, function ($path) use ($set) {
                $realPath = $set->getRealPath($path);

                return file_exists($realPath);
            });
            $countAfter = count($map);

            file_put_contents($writePath, json_encode($map, \JSON_PRETTY_PRINT));

            $output->writeln(sprintf('Re-wrote map to: <info>%s</info>', $writePath));
            $output->writeln(sprintf('Cleaned in %.3fs (removed %d entries)', microtime(true) - $startTime, $countBefore - $countAfter));
        } else {
            $hasher = new FileHasher();
            $gen    = new IntegrityMapGenerator($set, $hasher);

            $output->writeln('Generating map... This might take a while.');
            $startTime = microtime(true);
            $map       = $gen->generateMap();

            $writePath = $DP_ENV->getAppBaseKernelCacheDir().DIRECTORY_SEPARATOR.'integrity_file_map.dat';
            file_put_contents($writePath, json_encode($map, \JSON_PRETTY_PRINT));

            $output->writeln(sprintf('Wrote map to: <info>%s</info>', $writePath));
            $output->writeln(sprintf('Generated map of %d files in %.3fs', count($map), microtime(true) - $startTime));
        }
    }
}
