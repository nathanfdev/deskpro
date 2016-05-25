<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

use Application\DeskPRO\App\Native\NativeAppsSync;
use Application\DeskPRO\App\Package\PackageInstaller;
use Application\DeskPRO\DataSync\AbstractDataSync;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Languages\LangPackInfo;
use Application\InstallBundle\Data\DefaultDataProcessor;
use Application\InstallBundle\Upgrade\Build\AbstractBuild;
use DeskPRO\Bundle\PortalBundle\Designer\AdvancedEditsManager;
use DeskPRO\Bundle\PortalBundle\Designer\PortalStylesCompiler;
use DeskPRO\Component\Util\TypeUtils;
use DpSys\LowError\SystemErrorHandler;
use Monolog\Logger;
use Orb\Util\Arrays;

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
 * The filesystem contains a file /app/sys/config/build-time.php that defines DP_BUILD_TIME.
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
     * Runs the next build script.
     *
     * @param int $buildId
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    public function runBuild($buildId)
    {
        $class = $this->getBuildClass($buildId);
        /** @var AbstractBuild $build */
        $build = new $class($this->container, $this->logger);
        $ts    = microtime(true);
        if ($this->logger) {
            $this->logger->info(sprintf('********** #%s :: %s :: Begin **********', $buildId, TypeUtils::getBaseTypeName($build)));
        }

        try {
            $build->run();
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error(sprintf('EXCEPTION: %s [%s] %s', get_class($e), $e->getCode(), $e->getMessage()));
                $trace = SystemErrorHandler::formatBacktrace($e->getTrace());
                $this->logger->debug($trace);
            }

            throw $e;
        }

        if ($build->shouldRerun()) {
            $currentRun = $build->getStatus('runcount', 0);
            $nextRun    = $currentRun + 1;
            if ($this->logger) {
                $this->logger->debug(sprintf('runBuild(%d.%d)', $buildId, $nextRun));
            }
            $build->saveStatus('runcount', $currentRun + 1);
        } else {
            if ($this->logger) {
                $this->logger->debug(sprintf('Set core.deskpro_build = %s', $buildId));
            }
            $this->dbVersion = $buildId;
            $this->container->getDb()->update('settings', ['value' => $buildId], ['name' => 'core.deskpro_build']);
            $this->container->getDb()->executeUpdate('DELETE FROM import_datastore WHERE typename LIKE ?', [
                'up.'.$build->getBuildId().'.%',
            ]);
        }

        if ($this->logger) {
            $this->logger->info(sprintf('.......... #%s :: %s :: Done in %.3fs', $buildId, TypeUtils::getBaseTypeName($build), microtime(true) - $ts));
            $this->logger->info('');
        }
    }

    /**
     * Any code to be run after all upgrade scripts are complete (and we're on the latest DB).
     */
    public function postUpgrade()
    {
        if ($this->logger) {
            $this->logger->debug('Post upgrade begin');
        }

        AbstractDataSync::syncAllBaseToLive();

        // Update lang titles and has_agent flags
        $langPacks = new LangPackInfo();

        foreach ($langPacks->getLangTitles(true) as $id => $title) {
            if ($this->logger) {
                $this->logger->debug(sprintf('lang(%s).title = %s', $title, $id));
            }
            $this->container->getDb()->executeUpdate("UPDATE languages SET title = ? WHERE sys_name = ? AND title = ''", [$title, $id]);

            $info = $langPacks->getLangInfo($id);
            $this->container->getDb()->executeUpdate('UPDATE languages SET has_user = ?, has_agent = ?, has_admin = ? WHERE sys_name = ?', [$info['has_user'], $info['has_agent'], $info['has_admin'], $id]);
        }

        // Update flags if theyre blank
        $blankFlags = $this->container->getDb()->fetchAllCol("SELECT sys_name FROM languages WHERE flag_image = ''");
        foreach ($blankFlags as $sysName) {
            if (!$langPacks->hasLang($sysName)) {
                continue;
            }

            $flag = $langPacks->getLangInfo($sysName, 'flag_image');
            if ($flag) {
                if ($this->logger) {
                    $this->logger->debug(sprintf('lang(%s).flag = %s', $flag, $sysName));
                }
                $this->container->getDb()->executeUpdate('UPDATE languages SET flag_image = ? WHERE sys_name = ?', [$flag, $sysName]);
            }
        }

        // Auto-install any new langs
        $autoInstall = $this->container->getDb()->fetchColumn("SELECT value FROM settings WHERE name = 'core.lang_auto_install'");
        if ($autoInstall) {
            if ($this->logger) {
                $this->logger->debug('running lang auto-install');
            }
            $this->container->getEm()->getRepository(Language::class)->installAll($langPacks);
        }

        if ($this->logger) {
            $this->logger->debug('invalidate lang cache');
        }

        if ($this->logger) {
            $this->logger->debug('invalidate lang js cache');
        }

        #------------------------------
        # Data
        #------------------------------

        $dataProcessor = new DefaultDataProcessor($this->container);
        if ($this->logger) {
            $dataProcessor->setLogger($this->logger);
        }
        $dataProcessor->runSync();

        #------------------------------
        # Apps
        #------------------------------

        $appSyncer = new NativeAppsSync(
            $this->container,
            $this->container->getAppManager(),
            new PackageInstaller($this->container->getEm(), $this->container->getBlobStorage(), $this->container->getImagine()),
            $this->logger ?: null
        );

        // Dont fail the upgrade at this point
        // but log the error so we can know something went wrong with an app
        $appSyncer->setExceptionHandler(function ($e) {
            SystemErrorHandler::logException($e);
        });

        $appSyncer->runUpdates();
        $appSyncer->runSync();

        #------------------------------
        # Clear error logs
        #------------------------------

        foreach (['cli-phperr.log', 'server-phperr-web.log', 'error.log'] as $l) {
            $path = dp_get_log_dir().DIRECTORY_SEPARATOR.$l;
            if (file_exists($path)) {
                if ($this->logger) {
                    $this->logger->debug('resetting '.$l);
                }
                @file_put_contents($path, '');
            }
        }

        #------------------------------
        # Compile Custom Scss
        #------------------------------

        /** @var AdvancedEditsManager $advancedEditManager */
        $advancedEditManager = $this->container->get('dp.portal.designer.advanced_edits_manager');
        if ($advancedEditManager->getEditThemeSetScss()) {
            /** @var PortalStylesCompiler $styleCompiler */
            $styleCompiler = $this->container->get('dp.portal.designer.portal_styles_compiler');
            $styleCompiler->recompile([]);
            $this->logger->info('Compile Custom Scss scripts');
        }

        if ($this->logger) {
            $this->logger->debug('Post upgrade done');
        }
    }

    /**
     * Is there another build script to run?
     *
     * @return bool
     */
    public function hasNext()
    {
        $next_id = $this->getNextBuildId();

        return (bool) $next_id;
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
            $manifest = require DP_ROOT.'/src/Application/InstallBundle/Upgrade/Build/build-manifest.php';
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
     * Get a list of all upgrade build script available.
     *
     * @return array
     */
    public function getAllBuildIds()
    {
        if ($this->buildList !== null) {
            return $this->buildList;
        }

        $manifest        = require DP_ROOT.'/src/Application/InstallBundle/Upgrade/Build/build-manifest.php';
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
        return Arrays::getLastItem($this->getAllBuildIds());
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
}
