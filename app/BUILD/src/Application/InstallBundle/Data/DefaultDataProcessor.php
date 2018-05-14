<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\InstallBundle\Data;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Monolog\NullLogger;
use Application\InstallBundle\Data\DefaultData\AbstractDefaultData;
use Application\InstallBundle\Data\DefaultData\NoOpData;
use DpSys\CodePlugin\DpPlugins;
use Orb\Util\DpStrings;
use Orb\Util\Util;
use Psr\Log\LoggerInterface;

class DefaultDataProcessor
{
    /**
     * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    private $container;

    /**
     * @var string
     */
    private $root_dir;

    /**
     * @var array
     */
    private $data_classes;

    /**
     * @var array
     */
    private $data_info;

    /**
     * @var
     */
    private $logger;

    /**
     * @var array
     */
    private $extraOptions = [];

    public function __construct(DeskproContainer $container, $root_dir = null)
    {
        if ($root_dir === null) {
            $root_dir = DP_ROOT.'/src/Application/InstallBundle/Data/DefaultData';
        }

        $this->container = $container;
        $this->root_dir  = $root_dir;

        $this->logger = new NullLogger();
    }

    /**
     * @return array
     */
    public function getExtraOptions()
    {
        return $this->extraOptions;
    }

    /**
     * @param array $extraOptions
     */
    public function setExtraOptions($extraOptions)
    {
        $this->extraOptions = $extraOptions;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Loads install data if its not already loaded.
     */
    private function initDataInfo()
    {
        if ($this->data_info !== null) {
            return;
        }

        $row  = $this->container->getDb()->fetchAssoc("SELECT id, data FROM datastore WHERE name = 'sys.install.default_data' LIMIT 1");
        $data = null;
        if ($row) {
            $data = @unserialize($row['data']);
        }
        if (!$data) {
            $data = [];
        }

        if (!isset($data['installed'])) {
            $data['installed'] = [];
        }

        $this->data_info = $data;
    }

    /**
     * Save data info.
     */
    private function flushDataInfo()
    {
        $this->container->getDb()->delete('datastore', ['name' => 'sys.install.default_data']);
        $this->container->getDb()->insert('datastore', [
            'name' => 'sys.install.default_data',
            'auth' => DpStrings::random(15),
            'data' => serialize($this->data_info),
        ]);
    }

    /**
     * @param string $classname
     *
     * @return bool
     */
    public function isInstalled($classname)
    {
        $this->initDataInfo();

        return in_array($classname, $this->data_info['installed']);
    }

    /**
     * @return array
     */
    public function getDataClasses()
    {
        if ($this->data_classes !== null) {
            return $this->data_classes;
        }

        $this->data_classes = [];

        $dir = dir($this->root_dir);
        while (($f = $dir->read()) !== false) {
            if ($f == '.' || $f == '..' || strpos($f, 'Abstract') !== false) {
                continue;
            }
            $classname = 'Application\\InstallBundle\\Data\\DefaultData\\'.str_replace('.php', '', $f);

            if ($classname === NoOpData::class) {
                continue;
            }

            if (class_exists($classname)) {
                $this->data_classes[] = $classname;
            }
        }

        $this->data_classes = array_merge(
            $this->data_classes,
            DpPlugins::getManager()->getExtraDefaultDataClasses($this)
        );

        usort($this->data_classes, function ($a, $b) {
            $pri_a = $a::PRIORITY;
            $pri_b = $b::PRIORITY;

            if ($pri_a == $pri_b) {
                return 0;
            }

            return $pri_a < $pri_b ? -1 : 1;
        });

        return $this->data_classes;
    }

    /**
     * Installs new data classes that havent been marked as installed yet.
     *
     * @param string $specific_class Only run this specific data class
     */
    public function runInstall($specific_class = null)
    {
        if ($specific_class) {
            $specific_class = strtolower($specific_class);
        }

        foreach ($this->getDataClasses() as $classname) {
            if ($this->isInstalled($classname)) {
                continue;
            }

            if ($specific_class) {
                if (strtolower($classname) != $specific_class && strtolower(Util::getBaseClassname($classname)) != $specific_class) {
                    continue;
                }
            }

            $this->logger->info('Running install on '.Util::getBaseClassname($classname));
            $start_time = microtime(true);

            $obj = $this->createDataClass($classname);
            $obj->runInstall();

            $this->data_info['installed'][] = $classname;
            $this->flushDataInfo();

            $this->logger->info(sprintf('... done in %.4fs', microtime(true) - $start_time));
        }
    }

    /**
     * Installs new data classes that havent been marked as installed yet, or runs sync if it has.
     *
     * @param string $specific_class Only run this specific data class
     */
    public function runSync($specific_class = null)
    {
        if ($specific_class) {
            $specific_class = strtolower($specific_class);
        }

        $didRun = false;

        foreach ($this->getDataClasses() as $classname) {
            if ($specific_class) {
                if (strtolower($classname) != $specific_class && strtolower(Util::getBaseClassname($classname)) != $specific_class) {
                    continue;
                }
            }

            $didRun = true;

            if (!$this->isInstalled($classname)) {
                $this->logger->info('Running install via upgrade on '.Util::getBaseClassname($classname));
                $start_time = microtime(true);

                $obj = $this->createDataClass($classname);
                $obj->runInstallViaUpgrade();

                $this->data_info['installed'][] = $classname;
                $this->flushDataInfo();

                $this->logger->info(sprintf('... done in %.4fs', microtime(true) - $start_time));
            } else {
                $this->logger->info('Running sync on '.Util::getBaseClassname($classname));
                $start_time = microtime(true);

                $obj = $this->createDataClass($classname);
                $obj->runSync();

                $this->logger->info(sprintf('... done in %.4fs', microtime(true) - $start_time));
            }
        }

        if (!$didRun && $specific_class) {
            $this->logger->error("Unknown data class: $specific_class");
        }
    }

    /**
     * Resets data classes.
     *
     * @param string $specific_class Only run this specific data class
     */
    public function runReset($specific_class = null)
    {
        if ($specific_class) {
            $specific_class = strtolower($specific_class);
        }

        foreach ($this->getDataClasses() as $classname) {
            if ($specific_class) {
                if (strtolower($classname) != $specific_class && strtolower(Util::getBaseClassname($classname)) != $specific_class) {
                    continue;
                }
            }

            if (!$this->isInstalled($classname)) {
                $this->logger->info('Running install via upgrade on '.Util::getBaseClassname($classname));
                $start_time = microtime(true);

                $obj = $this->createDataClass($classname);
                $obj->runInstallViaUpgrade();

                $this->data_info['installed'][] = $classname;
                $this->flushDataInfo();

                $this->logger->info(sprintf('... done in %.4fs', microtime(true) - $start_time));
            } else {
                $this->logger->info('Running reset on '.Util::getBaseClassname($classname));
                $start_time = microtime(true);

                $obj = $this->createDataClass($classname);
                $obj->runReset();

                $this->logger->info(sprintf('... done in %.4fs', microtime(true) - $start_time));
            }
        }
    }

    /**
     * @param string $classname
     *
     * @return AbstractDefaultData
     */
    private function createDataClass($classname)
    {
        $classname = DpPlugins::getManager()->getRewrittenClassName($classname);

        return new $classname($this->container, $this->logger, $this->extraOptions);
    }
}
