<?php

namespace DeskPRO\Bundle\UpdateBundle\Service;

use DeskPRO\Bundle\UpdateBundle\Instance\InstanceReader;
use DeskPRO\Component\Util\ListUtils;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class UpdateCleanup
{
    /**
     * @var string
     */
    protected $current;

    /**
     * @var InstanceReader
     */
    protected $reader;

    public function __construct(Container $container)
    {
        $this->current = $container->get('deskpro.app_env')->getAppName();
        $this->reader  = $container->get('dp.updater.instance_reader');
    }

    /**
     * @param bool            $doRun  Set to true to actually clean. otherwise this is a dry run and will return the dirs to clean
     * @param LoggerInterface $logger
     *
     * @return \SplFileInfo[]
     */
    public function cleanup($doRun = true, LoggerInterface $logger = null)
    {
        if (!$logger) {
            $logger = new NullLogger();
        }

        if (defined('DPC_IS_CLOUD')) {
            $logger->info('Cloud; skipping.');

            return [];
        }

        if (!is_numeric($this->current)) {
            $logger->info('Current build is non-numeric; skipping.');

            return [];
        }

        $installedBuilds = ListUtils::map(
            Finder::create()
                ->directories()
                ->name('/^[0-9]+$/')
                ->in([$this->reader->getAppBasePath()])
                ->depth(0),
            function (\SplFileInfo $dir) {
                return (int) $dir->getBasename();
            }
        );

        if (count($installedBuilds) <= 2) {
            $logger->info('<= 2 builds installed; no cleanup necessary.');

            return [];
        }

        if (!in_array($this->current, $installedBuilds)) {
            $logger->info('Failed to read current build state; aborting.');

            return [];
        }

        sort($installedBuilds, \SORT_NUMERIC);
        $installedBuilds = array_slice($installedBuilds, 0, array_search($this->current, $installedBuilds));
        $prevBuild       = array_pop($installedBuilds); // gets the previously installed build

        // failsafe
        if ($prevBuild == $this->current || (defined('DP_ACTIVE_BUILD') && $prevBuild == DP_ACTIVE_BUILD)) {
            return [];
        }

        $ts = microtime(true);
        $logger->debug('Scanning filesystem');

        /** @var \SplFileInfo[] $cleanupDirs */
        $cleanupDirs = iterator_to_array(Finder::create()
            ->directories()
            ->name('/^[0-9]+$/')
            ->in([
                $this->reader->getAppBasePath(),
                $this->reader->getAssetsBasePath(),
                $this->reader->getKernelCacheBasePath(),
            ])
            ->depth(0)
            ->filter(function (\SplFileInfo $dir) use ($prevBuild) {
                $name = $dir->getBasename();
                $nameBuild = (int) $name;

                // failsafe
                if ($name == $this->current || (defined('DP_ACTIVE_BUILD') && $name == DP_ACTIVE_BUILD)) {
                    return false;
                }

                if (!is_numeric($name) || !$nameBuild) {
                    return false;
                }

                if ($name >= $prevBuild) {
                    return false;
                }

                return true;
            }));

        $logger->info(sprintf('Scanned filesystem in %.3fs. Found %d dirs that need cleaning', microtime(true) - $ts, count($cleanupDirs)));

        if ($cleanupDirs) {
            $logger->debug('Dirs: '.implode(', ', $cleanupDirs));
        }

        if ($doRun) {
            $logger->info(sprintf('Cleaning %d dirs...', count($cleanupDirs)));

            $cleanTs = microtime(true);

            $fs = new Filesystem();

            foreach ($cleanupDirs as $dir) {
                $dirTs = microtime(true);
                try {
                    $fs->remove($dir);
                    $logger->info(sprintf('Removed %s in %.3fs', $dir->getPathname(), microtime(true) - $dirTs));
                } catch (\Exception $e) {
                    $logger->warning(sprintf('FAILED to remove %s (time to failure: %.3fs). Error: %s', $dir->getPathname(), microtime(true) - $dirTs, $e->getMessage()));
                }
            }

            $logger->info(sprintf('Done all in %.3fs', microtime(true) - $cleanTs));
        }

        return $cleanupDirs;
    }
}
