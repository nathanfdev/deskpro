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
 *
 * @category Commands
 */
namespace Application\DeskPRO\Command;

use Application\DeskPRO\App;
use Application\DeskPRO\Monolog\Handler\OrbLoggerAdapterHandler;
use Application\InstallBundle\Data\DefaultDataProcessor;
use Doctrine\DBAL\DBALException;
use Monolog\Logger;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('dp:install');
        $this->addOption('insert-initial', null, InputOption::VALUE_NONE, 'Unused (exists for legacy)');
        $this->addOption('admin-email', null, InputOption::VALUE_OPTIONAL, 'Initial admin email');
        $this->addOption('admin-password', null, InputOption::VALUE_OPTIONAL, 'Initial admin password');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //TODO remove for prod
        echo "TODO chmod'ing cache dir, remove this in prod\n";
        passthru('chmod -R 0777 '.escapeshellarg(DP_ROOT.'/sys/cache'));
        $ret = $this->doExecute($input, $output);

        return $ret;
    }

    protected function doExecute(InputInterface $input, OutputInterface $output)
    {
        $output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);

        if (!$this->ensureNotInstalled()) {
            return 1;
        }

        $this->createDatabase();
        $this->getLogger()->log('Install::createTables', 'debug');

        $db    = $this->getDb();
        $check = $db->fetchColumn("SHOW TABLES LIKE 'install_data'");

        if ($check != 'install_data') {
            try {
                $db->exec("
                    CREATE TABLE `install_data` (
                      `build` varchar(30) NOT NULL,
                      `name` varchar(75) NOT NULL DEFAULT '',
                      `data` blob NOT NULL,
                      PRIMARY KEY (`build`,`name`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=latin1
                ");
            } catch (\Exception $e) {
                $this->getLogger()->log('Failed to craete install_data: '.$e->getCode().' '.$e->getMessage(), 'err');

                return;
            }

            $tableinfo = $db->fetchColumn('SHOW CREATE TABLE `install_data`', array(), 1);
            if (stripos($tableinfo, 'innodb') === false) {
                $this->getLogger()->log('install_data is not innodb', 'err');

                return;
            }
        }

        if (!defined('DP_BUILD_TIME')) {
            $build_file = DP_ROOT.'/sys/config/build-time.php';
            if (is_file($build_file)) {
                require $build_file;
            } else {
                define('DP_BUILD_TIME', time());
            }
        }

        $logger = new \Orb\Log\Logger();
        $schema = null;
        if (file_exists(DP_ROOT.'/src/Application/InstallBundle/Data/schema.php')) {
            $schema = require DP_ROOT.'/src/Application/InstallBundle/Data/schema.php';
        } else {
            $logger->log('schema.php does not exist, will auto-generate', 'debug');
        }

        $install_schema = new \Application\InstallBundle\Install\InstallSchema($this->getDb(), $schema, DP_BUILD_TIME);
        $install_schema->setLogger($logger);
        $install_schema->run(false);

        $this->loadSeedFixtures($output);
        $this->loadFixtures($output);

        $dev_fixtures = $this->getContainer()->getParameter('kernel.environment') === 'dev'
            || (is_file(DP_ROOT.'/sys/config/installer-type') && trim(file_get_contents(DP_ROOT.'/sys/config/installer-type')) === 'buildserver');

        if ($dev_fixtures) {
            $this->loadDevFixtures($output);
        }

        $this->loadDefaultData($logger);
        $this->installApps();

        if ($input->getOption('admin-email') and $input->getOption('admin-password')) {
            $db = $this->getContainer()->get('database_connection');
            $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');

            /** @var \Application\DeskPRO\Entity\Person $admin */
            $admin = $em->createQuery('SELECT p FROM DeskPRO:Person p WHERE p.can_admin = true ORDER BY p.id ASC')->setMaxResults(1)->getOneOrNullResult();

            if (!$admin) {
                $output->writeln('Could not find admin user to reset the password and email');

                return 1;
            }

            $admin->setPassword($input->getOption('admin-password'));
            $em->persist($admin);

            $admin->getPrimaryEmail()->setEmail($input->getOption('admin-email'));
            $em->persist($admin->getPrimaryEmail());

            // And we need to delete that special label that is used to
            // trigger the set password prompt on admin welcome guide
            $admin->removeLabelByString('not_user');

            $em->flush();
        }

        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $em->getRepository('DeskPRO:Ticket')->fillSearchTable();

        // A signifier setting that says we got to the end successfully
        // This isn't in a fixture itself because we want to make sure
        // we've go to the very end here without error
        $this->getDb()->insert('settings', [
            'name'  => 'installer.done',
            'value' => 1,
        ]);

        return 0;
    }

    private function loadDefaultData($logger)
    {
        $data_proc = new DefaultDataProcessor($this->getContainer());
        if ($logger) {
            $orb_logger_adapter = new OrbLoggerAdapterHandler($logger);
            $data_proc->setLogger(new Logger('data_proc', array($orb_logger_adapter)));
        }
        $data_proc->runInstall();

        \Application\DeskPRO\DataSync\AbstractDataSync::syncAllBaseToLive();
    }

    private function loadSeedFixtures(OutputInterface $output)
    {
        $output->writeln('Executing seed fixtures...');

        $app   = $this->getApplication();
        $input = new ArrayInput(array(
            'command'          => 'doctrine:fixtures:load',
            '--fixtures'       => DP_ROOT.'/src/DeskPRO/Bundle/AppBundle/DataFixtures/SeedFixtures',
            '--no-interaction' => true,
            '--append'         => true,
        ));
        $returnCode = $app->doRun($input, $output);

        $output->writeln('Fixtures exit code: '.$returnCode);
    }

    private function loadFixtures(OutputInterface $output)
    {
        $output->writeln('Executing install fixtures...');

        $app   = $this->getApplication();
        $input = new ArrayInput(array(
            'command'          => 'doctrine:fixtures:load',
            '--fixtures'       => DP_ROOT.'/src/DeskPRO/Bundle/AppBundle/DataFixtures/InstallFixtures',
            '--no-interaction' => true,
            '--append'         => true,
        ));
        $returnCode = $app->doRun($input, $output);

        $output->writeln('Fixtures exit code: '.$returnCode);
    }

    private function loadDevFixtures(OutputInterface $output)
    {
        $output->writeln('Executing dev fixtures...');

        $app   = $this->getApplication();
        $input = new ArrayInput(array(
            'command'          => 'doctrine:fixtures:load',
            '--fixtures'       => DP_ROOT.'/src/DeskPRO/Bundle/AppBundle/DataFixtures/DevFixtures',
            '--no-interaction' => true,
            '--append'         => true,
        ));
        $returnCode = $app->doRun($input, $output);

        $output->writeln('Fixtures exit code: '.$returnCode);
    }

    private function installApps()
    {
        $app_syncer = new \Application\DeskPRO\App\Native\NativeAppsSync(
            $this->getContainer(),
            $this->getContainer()->getAppManager(),
            new \Application\DeskPRO\App\Package\PackageInstaller($this->getContainer()->getEm(), $this->getContainer()->getBlobStorage(), $this->getContainer()->getImagine()),
            null
        );
        $app_syncer->runSync();

        $this->getContainer()->resetSystemService('app_manager');
        $instance_installer = new \Application\DeskPRO\App\InstanceInstaller(
            $this->getContainer()->getAppManager(),
            $this->getContainer()->getAppManager()->getPackage('deskpro_gravatar'),
            $this->getContainer()->getEm()
        );
        $instance_installer->install('', array(), $this->getContainer());
    }

    private function createDatabase()
    {
        try {
            App::getDb()->connect();
        } catch (\Exception $e) {
            if ($e instanceof DBALException || $e instanceof \PDOException) {
                if ($e->getCode() == '1049') {
                    // Attempt to create an empty database
                    try {
                        global $DP_CONFIG;
                        $dbh = new \PDO("mysql:host={$DP_CONFIG['db']['host']}", $DP_CONFIG['db']['user'], $DP_CONFIG['db']['password']);
                        $dbh->exec("CREATE DATABASE `{$DP_CONFIG['db']['dbname']}`");
                    } catch (\Exception $e) {
                    }
                }
            } else {
                throw $e;
            }
        }
    }

    public function ensureNotInstalled()
    {
        try {
            $this->getDb()->connect();

            $installed = $this->getDb()->fetchColumn('SELECT value FROM settings WHERE name = ?', array('core.install_timestamp'));
            if ($installed) {
                return false;
            }
        } catch (\Exception $e) {
            return true;
        }

        return true;
    }

    public function getDb()
    {
        return App::getDb();
    }

    public function getLogger()
    {
        return new \Orb\Log\Logger();
    }

    public function getOrm()
    {
        return App::getOrm();
    }
}
