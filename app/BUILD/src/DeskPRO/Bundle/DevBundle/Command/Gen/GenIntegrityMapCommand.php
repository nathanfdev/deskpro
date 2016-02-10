<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

        $writePath = $DP_ENV->getAppBaseKernelCacheDir().DIRECTORY_SEPARATOR.'integrity_file_map.dat';
        $set       = new ProjectFileSet($DP_ENV);

        if ($input->getOption('clean-missing') && file_exists($writePath)) {
            $map = json_decode(file_get_contents($writePath), true);

            $output->writeln('Cleaning map...');
            $startTime = microtime(true);
            $output->writeln(sprintf('Cleaned in %.3fs', microtime(true) - $startTime));

            MapUtils::filter($map, function ($path) use ($set) {
                $realPath = $set->getRealPath($path);

                return file_exists($realPath);
            });

            file_put_contents($writePath, json_encode($map, \JSON_PRETTY_PRINT));

            $output->writeln(sprintf('Re-wrote map to: <info>%s</info>', $writePath));
        } else {
            $hasher = new FileHasher();
            $gen    = new IntegrityMapGenerator($set, $hasher);

            $output->writeln('Generating map... This might take a while.');
            $startTime = microtime(true);
            $map       = $gen->generateMap();
            $output->writeln(sprintf('Generated map of %d files in %.3fs', count($map), microtime(true) - $startTime));

            $writePath = $DP_ENV->getAppBaseKernelCacheDir().DIRECTORY_SEPARATOR.'integrity_file_map.dat';
            file_put_contents($writePath, json_encode($map, \JSON_PRETTY_PRINT));

            $output->writeln(sprintf('Wrote map to: <info>%s</info>', $writePath));
        }
    }
}
