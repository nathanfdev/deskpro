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

namespace Application\InstallBundle\Upgrade;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\InstallBundle\Upgrade\Build\AbstractBuild;
use Application\InstallBundle\Upgrade\Build\AbstractImprovedBuild;
use DeskPRO\Component\Util\ListUtils;
use DeskPRO\Component\Util\TypeUtils;
use DpSys\LowError\SystemErrorHandler;
use Monolog\Logger;
use Psr\Log\NullLogger;

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
class Manager
{
    /**
     * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    protected $container;

    /**
     * @var int
     */
    protected $dbVersion;

    /**
     * @var array
     */
    protected $buildList;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var array
     */
    protected $manifest;

    /**
     * @param DeskproContainer $container
     * @param Logger           $logger
     */
    public function __construct(DeskproContainer $container, Logger $logger = null)
    {
        $this->container = $container;
        $this->logger    = $logger;
        $this->reset();
    }

    /**
     * @return int
     */
    public function getCurrentBuild()
    {
        return $this->dbVersion;
    }

    /**
     * When build info might've changed outside of this request, this rebuilds internal structures.
     */
    public function reset()
    {
        $this->dbVersion = $this->container->getDb()->fetchColumn("SELECT value FROM settings WHERE name = 'core.deskpro_build'");
    }

    /**
     * @return array
     */
    public function getManifest()
    {
        if ($this->manifest === null) {
            $this->manifest = require DP_ROOT.'/src/Application/InstallBundle/Upgrade/Build/build-manifest.php';
        }

        return $this->manifest;
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
        $build = $this->createBuildClass($buildId);

        $this->logger->info(sprintf('********** #%s :: %s :: Begin **********', $buildId, TypeUtils::getBaseTypeName($build)));

        $this->runBuildSteps($build);

        $this->logger->debug(sprintf('Set core.deskpro_build = %s', $buildId));

        $this->dbVersion = $buildId;
        $this->container->getDb()->update('settings', ['value' => $buildId], ['name' => 'core.deskpro_build']);

        $this->logger->info(sprintf('.......... #%s :: %s :: Done in %.3fs', $buildId, TypeUtils::getBaseTypeName($build), microtime(true) - $ts));
        $this->logger->info('');
    }

    /**
     * @param int $buildId
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    public function runOnlineBuild($buildId)
    {
        $ts    = microtime(true);
        $build = $this->createBuildClass($buildId);

        $this->logger->info(sprintf('********** #%s :: %s :: Begin ONLINE **********', $buildId, TypeUtils::getBaseTypeName($build)));

        if (!$build instanceof AbstractImprovedBuild) {
            $this->logger->warn('SKIPPED: Not an improved build script');

            return;
        }

        $this->runOnlineSteps($build);

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
        if ($build instanceof AbstractImprovedBuild) {
            $this->runOnlineSteps();
            $methods = ['runAlters', 'run'];
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

    /**
     * @param AbstractImprovedBuild $build
     *
     * @throws \Exception
     */
    private function runOnlineSteps(AbstractImprovedBuild $build)
    {
        foreach (['addNewTables', 'runBcAlters', 'runBc'] as $method) {
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

        // TODO need to store state about what was run
    }

    /**
     * @param $buildId
     *
     * @return AbstractBuild
     */
    private function createBuildClass($buildId)
    {
        $class = $this->getBuildClass($buildId);
        $build = new $class($this->container, $this->logger);

        return $build;
    }

    /**
     * Is there another build script to run?
     *
     * @return bool
     */
    public function hasNext()
    {
        $nextId = $this->getNextBuildId();

        return (bool) $nextId;
    }

    /**
     * Get the build class for a build ID.
     *
     * @param int $buildId
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getBuildClass($buildId)
    {
        $class = 'Application\\InstallBundle\\Upgrade\\Build\\Build'.$buildId;

        if (!class_exists($class, false)) {
            $manifest = $this->getManifest();
            if (isset($manifest[$buildId])) {
                $file  = DP_ROOT.$manifest[$buildId]['file'];
                $class = $manifest[$buildId]['classname'];
            } else {
                throw new \Exception("Unknown build. $buildId is not in the manifest.");
            }
            require_once $file;
        }

        return $class;
    }

    /**
     * Get an array of build IDs that are waiting to be performed.
     * The array is ordered.
     *
     * @return array
     */
    public function getWaitingBuildIds()
    {
        $ret = [];

        foreach ($this->getAllBuildIds() as $buildId) {
            if ($this->dbVersion < $buildId) {
                $ret[] = $buildId;
            }
        }

        return $ret;
    }

    /**
     * @param string $buildId
     *
     * @return array
     */
    public function getBuildInfo($buildId)
    {
        $manifest = $this->getManifest();

        return $manifest[$buildId];
    }

    /**
     * Get a list of all upgrade build script available.
     *
     * @return array
     */
    public function getAllBuildIds()
    {
        if ($this->buildList !== null) {
            return $this->buildList;
        }

        $manifest        = $this->getManifest();
        $this->buildList = array_keys($manifest);

        array_unique($this->buildList, \SORT_NUMERIC);
        sort($this->buildList, \SORT_NUMERIC);

        return $this->buildList;
    }

    /**
     * Gets the next build ID or 0 if the db is up to date.
     *
     * @return int
     */
    public function getNextBuildId()
    {
        foreach ($this->getAllBuildIds() as $buildId) {
            if ($this->dbVersion < $buildId) {
                return $buildId;
            }
        }

        return 0;
    }

    /**
     * Get the latest build id.
     *
     * @return int
     */
    public function getLatestBuildId()
    {
        return ListUtils::last($this->getAllBuildIds());
    }

    /**
     * Formats a build ID.
     *
     * @param int $buildId
     *
     * @return string
     */
    public function formatBuildId($buildId)
    {
        return date('Y-m-d H:i:s', $buildId);
    }

    /**
     * @return DeskproContainer
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        if (!$this->logger) {
            $this->logger = new NullLogger();
        }

        return $this->logger;
    }
}
