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

namespace DeskPRO\Bundle\InstallBundle\Installer\InstallStep;

use DeskPRO\Bundle\InstallBundle\InstallSession\Model\DbInfo;
use DeskPRO\Bundle\InstallBundle\InstallSession\Model\Paths;

/**
 * Reads info from config if --skip-wizard was specified.
 */
class SkipWizardStep extends AbstractStep
{
    public function run()
    {
        $env = $this->getContext()->getDpEnv();

        if (!$env->getConfig('database.host') && !$env->getConfig('database.0.host')) {
            $this->writeln('<error>No configuration files were found in the config/ directory</error>');
            $this->writeln('Using --skip-wizard assumes you have manually created config files.');
            $this->markAsFailed();
        }

        if (!$this->getSession()->getPaths()) {
            $paths                 = new Paths();
            $paths->php_path       = $env->getConfig('paths.php_path', 'php');
            $paths->mysqldump_path = $env->getConfig('paths.mysqldump_path', 'mysqldump');
            $paths->mysql_path     = $env->getConfig('paths.mysql_path', 'mysql');
            $this->getSession()->setPaths($paths);
        }

        // Try default database, create if doesn't exist

        if (!$this->getSession()->getDbInfo()) {
            $env              = $this->getContext()->getDpEnv();
            $dbinfo           = new DbInfo();
            $dbinfo->host     = $env->getConfig('database.host');
            $dbinfo->user     = $env->getConfig('database.user');
            $dbinfo->password = $env->getConfig('database.password');
            $dbinfo->dbname   = $env->getConfig('database.dbname');
            $this->getSession()->setDbInfo($dbinfo);
        } else {
            $dbinfo = $this->getSession()->getDbInfo();
        }

        try {
            $this->getSession()->getDbInfo()->getPdo();
        } catch (\Exception $e) {
            try {
                $pdo = $this->getSession()->getDbInfo()->getPdo(true);
                $pdo->exec('CREATE DATABASE `'.$dbinfo->dbname.'`');
            } catch (\Exception $e) {
            }
        }

        // Try system database, create if doesn't exist

        if (!$this->getSession()->getSystemDbInfo()) {
            $env                  = $this->getContext()->getDpEnv();
            $sys_dbinfo           = new DbInfo();
            $sys_dbinfo->host     = $env->getConfig('database.system.host') ?: $env->getConfig('database.host');
            $sys_dbinfo->user     = $env->getConfig('database.system.user') ?: $env->getConfig('database.user');
            $sys_dbinfo->password = $env->getConfig('database.system.password') ?: $env->getConfig('database.password');
            $sys_dbinfo->dbname   = $env->getConfig('database.system.dbname') ?: $env->getConfig('database.dbname');
            $this->getSession()->setSystemDbInfo($sys_dbinfo);
        } else {
            $sys_dbinfo = $this->getSession()->getSystemDbInfo();
        }

        try {
            $this->getSession()->getSystemDbInfo()->getPdo();
        } catch (\Exception $e) {
            try {
                $pdo = $this->getSession()->getSystemDbInfo()->getPdo(true);
                $pdo->exec('CREATE DATABASE `'.$sys_dbinfo->dbname.'`');
            } catch (\Exception $e) {
            }
        }
    }

    public function isComplete()
    {
        return false;
    }
}
