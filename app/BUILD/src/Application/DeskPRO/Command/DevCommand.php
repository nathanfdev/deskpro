<?php

/**
 * DeskPRO.
 *
 * @category Commands
 */

namespace Application\DeskPRO\Command;

use Application\DeskPRO\Email\EmailAccount\IncomingAccount\Pop3Config;
use Application\DeskPRO\Email\EmailAccount\OutgoingAccount\SmtpConfig;
use Application\InstallBundle\Util\GenBuildManifest;
use Orb\Types\JsonObjectSerializer;
use Orb\Util\Strings;
use Swagger\Swagger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class DevCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('dpdev');
        $this->addOption('regen-build-manifest', null, InputOption::VALUE_NONE, 'Regenerate build-manifest.php file');
        $this->addOption('sync-buildfile-manifest', null, InputOption::VALUE_NONE, 'Like regen-build-manifest, but also goes through PHP build files to make sure the classname matches the filename. Useful if you have mass-moved or renamed files manually.');
        $this->addOption('touch-build-time', null, InputOption::VALUE_NONE, 'Sets build-time.txt file to now');
        $this->addOption('testdb-safe', null, InputOption::VALUE_NONE, 'Removes or rewrites some common settings to make the database safe to use');
        $this->addOption('testdb-rewrite-emails', null, InputOption::VALUE_REQUIRED, 'Rewrites all email addresses to be at the domain provided. someone@example.com becomes someone-at-example-com@domain.com');
        $this->addOption('move-build-scripts', null, InputOption::VALUE_REQUIRED, 'Comma-separated list of build scripts to re-timestamp from now. This is useful when merging an old branch and you want to move buildscripts "up".');
        $this->addOption('build-api-docs', null, InputOption::VALUE_NONE, 'Builds Swagger resource files');
        $this->addOption('gen-upgradecode-for-tables', null, InputOption::VALUE_REQUIRED, 'Generates CREATE TABLE upgrade code for a list of tables');
        $this->addOption('preview', null, InputOption::VALUE_NONE, 'Preview');
    }

    /**
     * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    public function getContainer()
    {
        return parent::getContainer();
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('regen-build-manifest')) {
            return $this->regenBuildManifestAction($input, $output);
        } elseif ($input->getOption('sync-buildfile-manifest')) {
            return $this->syncBuildFileClassname($input, $output);
        } elseif ($input->getOption('touch-build-time')) {
            return $this->touchBuildTimeAction($input, $output);
        } elseif ($input->getOption('testdb-safe')) {
            return $this->testdbSafeAction($input, $output);
        } elseif ($input->getOption('testdb-rewrite-emails')) {
            return $this->testdbRewriteEmailsAction($input, $output);
        } elseif ($input->getOption('build-api-docs')) {
            return $this->buildApiDocsAction($input, $output);
        } elseif ($input->getOption('move-build-scripts')) {
            return $this->moveBuildScriptsAction($input, $output);
        } elseif ($input->getOption('gen-upgradecode-for-tables')) {
            return $this->genUpgradeCodeForTablesAction($input, $output);
        } else {
            $output->write('<error>Unknown command</error>');

            return 1;
        }
    }

    private function testdbSafeAction(InputInterface $input, OutputInterface $output)
    {
        $db = $this->getContainer()->getDb();

        $output->writeln('Nulling email accounts -> Blank POP3 account with mailcatcher smtp');

        $incoming       = new Pop3Config();
        $incoming->host = 'localhost';
        $incoming->port = '110';
        $incoming       = JsonObjectSerializer::serialize($incoming);

        $out       = new SmtpConfig();
        $out->host = 'localhost';
        $out->port = '1025';
        $out       = JsonObjectSerializer::serialize($out);

        $db->executeUpdate('UPDATE email_accounts SET incoming_account = ?, outgoing_account = ?', [$incoming, $out]);

        $output->writeln('-> OK');

        $output->writeln('Clearing out some tables');

        $tables = [
            'twitter_accounts',
            'twitter_accounts_followers',
            'twitter_accounts_friends',
            'twitter_accounts_person',
            'twitter_accounts_searches',
            'twitter_accounts_searches_statuses',
            'twitter_accounts_statuses',
            'twitter_accounts_statuses_notes',
            'twitter_statuses',
            'twitter_statuses_long',
            'twitter_statuses_mentions',
            'twitter_statuses_tags',
            'twitter_statuses_urls',
            'twitter_stream',
            'twitter_users',
            'twitter_users_followers',
            'twitter_users_friends',
            'result_cache',
            'page_view_log',
            'agent_activity',
            'sessions',
        ];

        $db->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($tables as $t) {
            try {
                echo "Delete from $t";
                $db->exec("DELETE FROM $t");
                echo "-> OK\n";
            } catch (\Exception $e) {
                echo "-> Fail: {$e->getMessage()}\n";
            }
        }
        foreach ($tables as $t) {
            try {
                echo "Truncate $t";
                $db->exec("TRUNCATE TABLE $t");
                echo "-> OK\n";
            } catch (\Exception $e) {
                echo "-> Fail: {$e->getMessage()}\n";
            }
        }
        $db->exec('SET FOREIGN_KEY_CHECKS = 1');

        $output->writeln('Removing pictures, css, other common blobs that will fail to laod');
        $db->executeUpdate('UPDATE people SET picture_blob_id = null');
        $db->executeUpdate('UPDATE departments SET avatar_blob_id = null');
        $db->executeUpdate('UPDATE agent_teams SET avatar_blob_id = null');
        $this->getContainer()->getSettingsHandler()->setSetting('core.favicon_blob_url', null);
        $output->writeln('-> OK');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    private function testdbRewriteEmailsAction(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Running...');
        $db = $this->getContainer()->getDb();

        $output->writeln("Setting 'comment' to the original email");
        $db->executeUpdate('UPDATE people_emails SET comment = email');
        $output->writeln('-> OK');

        $output->writeln('Replacing at character');
        $db->executeUpdate("UPDATE people_emails SET email = REPLACE(email, '@', '-at-')");
        $output->writeln('-> OK');

        $output->writeln('Replacing dots');
        $db->executeUpdate("UPDATE people_emails SET email = REPLACE(email, '.', '-')");
        $output->writeln('-> OK');

        $domain = $input->getOption('testdb-rewrite-emails');
        $output->writeln("Setting new domain: $domain");
        $db->executeUpdate("UPDATE people_emails SET email = CONCAT(email, '@$domain')");
        $output->writeln('-> OK');

        $output->writeln('All done');

        return 0;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    private function regenBuildManifestAction(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Use dpdev:update:gen-build-manifest command instead');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    private function syncBuildFileClassname(InputInterface $input, OutputInterface $output)
    {
        $manifest_path = DP_ROOT.'/src/Application/InstallBundle/Upgrade/Build/build-manifest.php';
        $builds_path   = DP_ROOT.'/src/Application/InstallBundle/Upgrade/Build';

        // regen first
        $gen  = new GenBuildManifest($builds_path);
        $file = $gen->getContents();
        file_put_contents($manifest_path, $file);

        $manifest = require $manifest_path;

        foreach ($manifest as $build_id => $info) {
            $build_file  = file_get_contents(DP_ROOT.$info['file']);
            $orig        = $build_file;
            $class_parts = explode('\\', $info['classname']);
            $name        = array_pop($class_parts);

            if (preg_match('#class\s*(.*?)\s*extends\s*AbstractBuild#', $build_file, $m)) {
                if ($m[1] !== $name) {
                    $build_file = str_replace($m[0], "class {$name} extends AbstractBuild", $build_file);
                }

                // Add a comment about the real build ID if the filename isnt it
                if (!Strings::endsWith('Build'.$build_id.'.php', $info['file'])) {
                    $build_file = preg_replace('#//\[\[build:\d+\]\]#', '', $build_file);
                    $build_file = trim($build_file);
                    $build_file .= "\n\n//[[build:$build_id]]\n";
                }

                if ($orig !== $build_file) {
                    file_put_contents(DP_ROOT.$info['file'], $build_file."\n");
                }
            } else {
                echo "{$info['file']} contains an invlaid build definition.";
                die(1);
            }
        }
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    private function touchBuildTimeAction(InputInterface $input, OutputInterface $output)
    {
        $time       = time();
        $build_file = DP_ROOT.'/sys/config/build-time.txt';
        file_put_contents($build_file, $time);

        echo "Updated: $build_file\n";

        return 0;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    private function genUpgradeCodeForTablesAction(InputInterface $input, OutputInterface $output)
    {
        echo "!! Make sure the schema file is up to date: php app/bin/build/build-schema-file.php\n\n";

        $f = DP_ROOT.'/src/Application/InstallBundle/Data/schema.php';
        if (!$f) {
            echo "Run the above command first.\n";

            return 1;
        }

        echo "\n\n\n";

        $schema = require $f;

        $tables = array_map('trim', explode(',', $input->getOption('gen-upgradecode-for-tables')));

        foreach ($schema['create'] as $sql) {
            foreach ($tables as $t) {
                if (strpos($sql, 'CREATE TABLE '.$t.' ') !== false) {
                    echo '$this->execMutateSql("';
                    echo str_replace('"', '\\"', $sql);
                    echo '");';
                    echo "\n";
                }
            }
        }
        echo "\n\n";
        foreach ($schema['alter'] as $sql) {
            foreach ($tables as $t) {
                if (strpos($sql, 'ALTER TABLE '.$t.' ') !== false) {
                    echo '$this->execMutateSql("';
                    echo str_replace('"', '\\"', $sql);
                    echo '");';
                    echo "\n";
                }
            }
        }

        return 0;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    private function buildApiDocsAction(InputInterface $input, OutputInterface $output)
    {
        $start_time = microtime(true);

        $save_path = DP_ROOT.'/src/Application/LegacyApiBundle/Resources/views/SwaggerDocs';

        $output->writeln('Generating Swagger resources');
        $output->writeln("-> Path: $save_path");

        $output->writeln('Removing old files');
        $fs = new Filesystem();
        $fs->remove($save_path);
        $fs->mkdir($save_path, 0755);
        $output->writeln('-> OK');

        $output->writeln('Scanning ...');
        $swagger = new Swagger(DP_ROOT.'/src/Application/LegacyApiBundle');
        $output->writeln('-> OK');

        $output->writeln('Generating resource-list.json...');
        file_put_contents($save_path.'/deskpro-api.json', $swagger->getResourceList(['output' => 'json']));
        $fs->chmod($save_path.'/deskpro-api.json', 0644);

        $output->writeln('-> OK');

        foreach ($swagger->getResourceNames() as $res) {
            $output->writeln("Generating $res.json...");
            file_put_contents($save_path."/$res.json", $swagger->getResource($res, ['output' => 'json']));
            $fs->chmod($save_path."/$res.json", 0644);
            $output->writeln('-> OK');
        }

        $output->writeln(sprintf('All done in %.4fs', microtime(true) - $start_time));

        return 0;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    private function moveBuildScriptsAction(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Use dpdev:update:move-builds instead');
    }
}
