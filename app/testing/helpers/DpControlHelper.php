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

namespace Codeception\Module;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader as DataFixturesLoader;

class DpControlHelper extends \Codeception\Module
{
    /**
     * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    public function getSymfonyContainer()
    {
        return \DpTestEnv::getContainer();
    }

    /**
     * Sets the database set to a version of the set. If it already exists,
     * it will be re-used (not recreated).
     *
     * @param string $set_name
     */
    public function enableDatabaseSet($set_name)
    {
        \DpTestEnv::enableDatabaseSet($set_name);
    }

    /**
     * Like enableDatabaseSet but will always use a freshly built db.
     *
     * @param string $set_name
     */
    public function enableFreshDatabaseSet($set_name)
    {
        \DpTestEnv::enableDatabaseSet($set_name, true);
    }

    /**
     * Like enableDatabaseSet except this will reset the database set afterwards.
     *
     * @param string $set_name
     * @param bool   $reset    True to mark the db for reset
     */
    public function enableDestructiveDatabaseSet($set_name, $reset = false)
    {
        \DpTestEnv::enableDatabaseSet($set_name, $reset, true);
    }

    /**
     * Returns the currently set database back to the default.
     */
    public function useDefaultDatabase()
    {
        \DpTestEnv::restoreDefaultDb();
    }

    /**
     * @return int
     */
    public function getContainerCounter()
    {
        return \DpTestEnv::getCountainerCounter();
    }

    /**
     * Loads fixtures into the current database.
     * Note that this will mark the database to be reset.
     *
     * @param array $f...
     *
     * @throws \InvalidArgumentException
     */
    public function loadFixtures($f)
    {
        $fixtures = array();

        $args = func_get_args();
        foreach ($args as $a) {
            if (is_array($a)) {
                $fixtures = array_merge($fixtures, $a);
            } else {
                $fixtures[] = $a;
            }
        }

        $fixtures = array_unique($fixtures);

        \DpTestEnv::getContainer()->getDb()->exec("
            REPLACE INTO settings (name, value)
            VALUES ('core.dp_testing_dbset_resetafter', '1')
        ");

        $paths = array();
        foreach ($fixtures as $f) {
            $f       = str_replace('\\', '/', $f);
            $paths[] = DP_ROOT.'/testing/data/DpFixtures/'.$f;
        }

        $loader = new DataFixturesLoader(\DpTestEnv::getContainer());
        foreach ($paths as $path) {
            if (is_dir($path)) {
                $loader->loadFromDirectory($path);
            } elseif (is_file($path.'.php')) {
                require_once $path.'.php';
                $class = str_replace(DP_ROOT.'/testing/data/', '', $path);
                $class = str_replace('/', '\\', $class);
                $loader->addFixture(new $class());
            }
        }
        $fixtures = $loader->getFixtures();
        if (!$fixtures) {
            throw new \InvalidArgumentException(
                sprintf('Could not find any fixtures to load in: %s', "\n\n- ".implode("\n- ", $paths))
            );
        }

        $executor = new ORMExecutor(\DpTestEnv::getContainer()->getEm(), null);
        $me       = $this;
        $executor->setLogger(function ($message) use ($me) {
            $me->_dp_debugSection('loadFixtures', $message);
        });
        $executor->execute($fixtures, true);
    }

    public function _dp_debugSection($title, $message)
    {
        $this->debugSection($title, $message);
    }

    public function indexElasticsearch()
    {
        $indexManager     = $this->getSymfonyContainer()->get('fos_elastica.index_manager');
        $providerRegistry = $this->getSymfonyContainer()->get('fos_elastica.provider_registry');
        $resetter         = $this->getSymfonyContainer()->get('fos_elastica.resetter');

        $index = 'deskpro';

        $resetter->resetIndex($index);
        $providers = $providerRegistry->getIndexProviders($index);

        foreach ($providers as $type => $provider) {
            $provider->populate(null, array());
        }

        $resetter->postPopulate($index);
        $indexManager->getIndex($index)->refresh();
    }
}
