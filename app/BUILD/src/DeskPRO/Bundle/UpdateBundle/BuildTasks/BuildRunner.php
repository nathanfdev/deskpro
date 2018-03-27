<?php

namespace DeskPRO\Bundle\UpdateBundle\BuildTasks;

use Application\InstallBundle\Upgrade\Build\AbstractBuild;
use Application\InstallBundle\Upgrade\Build\BlockingBuildInterface;
use Application\InstallBundle\Upgrade\Build\OnlineBuildInterface;
use DeskPRO\Component\Util\TypeUtils;
use DpSys\LowError\SystemErrorHandler;
use Monolog\Logger;

/**
 * This manages database upgrade scripts. An upgrade script is just a class with queries to process
 * the database from a previous version into the current version. It brings the database up to date
 * with whatever the current filesystem version is.
 *
 * Database upgrade scripts are timestamps just like the deskpro build is. But although we call these
 * upgrade scripts "Build scripts," they do not directly correlate with official builds. That is,
 * a build scripts timestamp is typically the timestamp at which the dev implemented it, while a
 * deskpro package (zip file) build time is when it was actually generated and packaged.
 *
 * The database contains a setting `core.deskpro_build`. We call this the "database version".
 * The filesystem contains a file /app/sys/config/build-time.txt that defines DP_BUILD_TIME.
 * We call this the "filesystem version"
 *
 * This manager simply detects when the database version is older than the filesystem time,
 * and then runs all upgrade classes between the two points.
 */
class BuildRunner
{
    /**
     * @var BuildFactory
     */
    private $buildFactory;

    /**
     * @var ManifestReader
     */
    private $manifestReader;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * Manager constructor.
     *
     * @param BuildFactory   $buildFactory
     * @param ManifestReader $manifestReader
     * @param Logger         $logger
     */
    public function __construct(BuildFactory $buildFactory, ManifestReader $manifestReader, Logger $logger)
    {
        $this->buildFactory   = $buildFactory;
        $this->manifestReader = $manifestReader;
        $this->logger         = $logger;
    }

    /**
     * Runs the next build script.
     *
     * @param int $buildId
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    public function runBuild($buildId)
    {
        $ts    = microtime(true);
        $build = $this->buildFactory->createBuild($buildId);

        $this->logger->info(sprintf('********** #%s :: %s :: Begin **********', $buildId, TypeUtils::getBaseTypeName($build)));

        $this->runBuildSteps($build);

        $this->logger->debug(sprintf('Set core.deskpro_build = %s', $buildId));

        $this->logger->info(sprintf('.......... #%s :: %s :: Done in %.3fs', $buildId, TypeUtils::getBaseTypeName($build), microtime(true) - $ts));
        $this->logger->info('');
    }

    /**
     * @param AbstractBuild $build
     *
     * @throws \Exception
     */
    private function runBuildSteps(AbstractBuild $build)
    {
        if ($build instanceof OnlineBuildInterface || $build instanceof BlockingBuildInterface) {
            $methods = ['addNewTables', 'runAlters', 'run'];
        } else {
            $methods = ['run'];
        }

        foreach ($methods as $method) {
            $stepTs = microtime(true);
            $this->logger->info(sprintf('-- %s Begin --', $method));

            try {
                $build->$method();
            } catch (\Exception $e) {
                $this->logger->error(sprintf('EXCEPTION: %s [%s] %s', get_class($e), $e->getCode(), $e->getMessage()));
                $trace = SystemErrorHandler::formatBacktrace($e->getTrace());
                $this->logger->debug($trace);

                throw $e;
            }

            $this->logger->info(sprintf('-- %s Done in %.3fs --', $method, microtime(true) - $stepTs));
        }
    }
}
