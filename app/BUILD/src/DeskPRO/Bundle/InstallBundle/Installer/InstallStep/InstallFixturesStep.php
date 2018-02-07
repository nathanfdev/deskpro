<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

use DeskPRO\Bundle\InstallBundle\InstallSession\InstallSession;
use Orb\Util\DpStrings;
use Orb\Util\Strings;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Class InstallFixturesStep.
 */
class InstallFixturesStep extends AbstractStep
{
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        ini_set('memory_limit', '512M');

        $this->writeBigTitle('Initializing database');

        $this->checkAutoIncrementValue();
        if ($this->isFailed()) {
            return;
        }

        $this->writeln('We will now initialize the database. This may take a few minutes.');

        $fixtures = [
            'InstallFixtures',
            'SeedFixtures',
        ];

        $is_dev = $this->getSession()->getSource() === InstallSession::SOURCE_DEV
            || $this->getSession()->getSource() === InstallSession::SOURCE_BUILDSERVER;

        if ($is_dev) {
            $fixtures[] = 'DevFixtures';
        }

        $builder = new ProcessBuilder([
            $this->getSession()->getPaths()->php_path,
            $this->getContext()->getDpEnv()->getDpRoot().'/bin/console',
            'doctrine:fixtures:load',
            '--no-interaction',
            '--append',
            '--verbose',
        ]);

        $builder->setTimeout(10 * 60);

        foreach ($fixtures as $f) {
            $builder->add('--fixtures')->add($path = DP_APP_DIR.'/src/DeskPRO/Bundle/AppBundle/DataFixtures/'.$f);
        }

        $progress = $this->createProgressBar();
        $progress->setFormat('Initializing ... [%bar%]');
        $progress->setRedrawFrequency(1);
        $progress->setBarWidth(5);

        $proc = $builder->getProcess();

        $proc->start();
        $proc->wait(function () use ($progress) {
            $progress->advance();
        });

        $progress->clear();
        $this->writeln('');

        if (!$proc->isSuccessful()) {
            $this->writeln('<error>Failed to initialize database</error>');
            $this->markAsFailed();

            $this->writeln('<info>'.$proc->getCommandLine().'</info>');
            $this->writeln($proc->getOutput());
            $this->writeln($proc->getErrorOutput());

            // Failure here means we need to reinstall db
            $this->getSession()->enableFlag('reset_db_details');

            return;
        } elseif ($is_dev) {
            // display fixtures order in dev mode
            $this->writeln('<info>'.$proc->getCommandLine().'</info>');
            $this->writeln($proc->getOutput());
        }

        $this->installOptions();

        $this->writeln('Done!');

        $this->getSession()->enableFlag('install_fixtures_ok');
    }

    /**
     * {@inheritdoc}
     */
    public function isComplete()
    {
        return $this->getSession()->hasFlag('install_fixtures_ok');
    }

    private function installOptions()
    {
        $db = $this->getContext()->getMainContainer()->get('database_connection');

        $db->delete('settings', ['name' => 'core.install_source']);
        $db->insert('settings', [
            'name'  => 'core.install_source',
            'value' => $this->getContext()->getSession()->getSource() ?: 'default',
        ]);

        //---------------------------------------------
        // Filestorage method
        //---------------------------------------------

        $fsMethod = $this->getContext()->getProfile()->getAnswer('filestorage_method');

        if ($fsMethod) {
            $db->delete('settings', ['name' => 'core.filestorage_method']);

            switch ($fsMethod) {
                case 'fs':
                    $db->insert('settings', ['name' => 'core.filestorage_method', 'value' => 'fs']);
                    break;

                case 's3':
                    $db->insert('settings', ['name' => 'core.filestorage_method', 'value' => 's3']);
                    $db->executeUpdate("DELETE FROM settings WHERE name LIKE 'core.filestorage_s3%'");

                    $fsOptions = @json_decode(
                        $this->getContext()->getProfile()->getAnswer('filestorage_options') ?: '{}',
                        true
                    );

                    foreach ([
                        's3_key' => 'core.filestorage_s3_key',
                        's3_secret' => 'core.filestorage_s3_secret',
                        's3_bucket' => 'core.filestorage_s3_bucket',
                        's3_basepath' => 'core.filestorage_s3_basepath',
                        's3_file_url_domain' => 'core.filestorage_s3_file_url_domain',
                    ] as $optionName => $settingName) {
                        if (!empty($fsOptions[$optionName])) {
                            $db->insert('settings', [
                                'name'  => $settingName,
                                'value' => $fsOptions[$optionName],
                            ]);
                        }
                    }
                    break;
            }
        }

        //---------------------------------------------
        // Brand / URL
        //---------------------------------------------

        $brandId = $db->fetchColumn("SELECT value FROM settings WHERE name = 'portal.default_brand'");
        $db->delete('settings', ['name' => 'core.deskpro_url']);
        $db->delete('settings_brand', ['name' => 'core.deskpro_url']);

        // And insert the web url
        $url = $this->getSession()->getWebUrl();
        if (!$url) {
            $url = $this->getContext()->getProfile()->getAnswer('web_url');
        }
        if (!$url) {
            $url = 'http://deskpro-dev/';
        }
        $db->executeUpdate(
            'INSERT INTO settings (name, value) VALUES (?, ?)',
            ['core.deskpro_url', $url]
        );
        $db->executeUpdate(
            'INSERT INTO settings_brand (name, brand_id, value) VALUES (?, ?, ?)',
            ['core.deskpro_url', $brandId, $url]
        );
    }

    /**
     * Checks if auto_increment values are sequential. Marks step as failed if not.
     */
    private function checkAutoIncrementValue()
    {
        $db = $this->getContext()->getMainContainer()->get('database_connection');
        $db->insert('datastore', [
            'name' => 'test 1',
            'auth' => DpStrings::random(15, Strings::CHARS_KEY),
            'data' => 'test 1',
        ]);
        $firstId = (int) $db->lastInsertId();

        $db->insert('datastore', [
            'name' => 'test 2',
            'auth' => DpStrings::random(15, Strings::CHARS_KEY),
            'data' => 'test 2',
        ]);
        $secondId = (int) $db->lastInsertId();

        if (($secondId - $firstId) !== 1) {
            $this->writeln('<error>Your database is configured to use non-sequential auto-increment values. This is common with multi-master configurations such as Galera Cluster.</error>');
            $this->writeln('<error>Please refer to this article for more information about how to use DeskPRO with a database cluster:</error>');
            $this->writeln('<error>https://support.deskpro.com/en/kb/articles/378</error>');
            $this->writeln('<error>The installation cannot continue until auto-increment values are sequential.</error>');

            $this->markAsFailed();
        }

        // Cleanup test operations
        $db->delete('datastore', ['id' => $firstId]);
        $db->delete('datastore', ['id' => $secondId]);
        $db->executeQuery('ALTER TABLE datastore AUTO_INCREMENT='.$firstId);
    }
}
