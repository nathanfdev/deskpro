<?php

namespace DeskPRO\Bundle\DevBundle\Command;

use DeskPRO\Component\Util\IpUtils;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class MakeCloneSafeCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('dpdev:make-clone-safe')
            ->setDescription('This runs against a database and disables triggers, ensures email is disabled, etc.')
            ->addOption('with-safe-emails', null, InputOption::VALUE_NONE, 'This will rewrite all emails and append ".xxx" to them. Works as a fail-safe against emailing users by accident if email needs to be turned on')
            ->addOption('with-clean', null, InputOption::VALUE_NONE, 'Cleans tables that dont matter like hit records or login logs. This can make dumps/imports faster if you need to constantly reset a db')
            ->addOption('with-password', null, InputOption::VALUE_NONE, 'This sets all user passwords to "password"')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //------------------------------
        // Check that we're safe
        //------------------------------

        $appEnv = $this->getContainer()->get('deskpro.app_env');

        $helper   = $this->getHelper('question');
        $question = new ConfirmationQuestion('This tool can be destructive. Make a backup first! Continue? ', false);

        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $globalSettings = $this->getContainer()->get('settings_resolver')->getGlobalSettings();
        if (
            $globalSettings->get('core.filestorage_method', 'db') !== 'db'
            && $globalSettings->get('core.filestorage_method', 'db') !== 'fs'
            && !$globalSettings->get('core.filestorage_disable_physical_delete', false)
        ) {
            $output->writeln('<error>Error: Blob storage is not db/fs. This means what you do may affect a real external storage system.</error>');
            $output->writeln('    Hint: Try override the storage to db with config.settings.php: $SETTINGS[\'core.filestorage_method\'] = \'fs\';');
            $output->writeln('    Hint: Or if you need to test storage, at least disable deleting: $SETTINGS[\'core.filestorage_disable_physical_delete\'] = true;');

            return 1;
        }

        if ($globalSettings->get('elastica.enabled')) {
            if (IpUtils::guessIsLocalNetworkHost($globalSettings->get('elastica.clients.default.host'))) {
                $output->writeln('<comment>Warning: Elastic is enabled, so you might get a bunch of errors if its mapped wrong for your dev env.</comment>');
                $output->writeln('    Hint: Try override disable with config.settings.php: $SETTINGS[\'elastica.enabled\'] = false;');
            } else {
                $output->writeln('<error>Error: Elastic is enabled on an external host. What you do in dev might affect the live index.</error>');
                $output->writeln('    Hint: Try override disable with config.settings.php: $SETTINGS[\'elastica.enabled\'] = false;');

                return 1;
            }
        }

        if ($this->hasNonDeskproNotifs()) {
            $output->writeln('<error>Error: Using non-deskpro delivery of notifications. This could mean you do something that sends notifications using an external service.</error>');
            $output->writeln('    Hint: Override in config.settings.php:');
            $output->writeln('
    $SETTINGS = array_merge($SETTINGS, [
        \'notification.settings.default_strategy\' => [
            \'strategy\' => \'immediate\',
            \'delivery\' => [
                \'db\',
            ],
        ],
    ]);
            ');

            return 1;
        }

        if (!$appEnv->getConfig('settings.disable_outgoing_email') && !$input->getOption('with-safe-emails')) {
            $output->writeln('<comment>Warning: Outgoing email is enabled but you are not using --with-safe-emails</comment>');
            $output->writeln('    Hint: add this to config.settings.php: $SETTINGS[\'disable_outgoing_email\'] = true;');
        }

        //------------------------------
        // Run clean
        //------------------------------

        $db = $this->getContainer()->get('database_connection');
        $output->writeln('Disabling triggers');
        $db->update('ticket_triggers', ['is_enabled' => 0], ['1' => '1']);

        $output->writeln('Disabling escalations');
        $db->update('ticket_escalations', ['is_enabled' => 0], ['1' => '1']);

        $output->writeln('Turning email accounts into Noop');
        $db->update('email_accounts', [
            'incoming_account' => '{"@CLASS":"Application\\DeskPRO\\Email\\EmailAccount\\IncomingAccount\\NoopConfig","@DATA":[]}',
            'outgoing_account' => '{"@CLASS":"Application\\DeskPRO\\Email\\EmailAccount\\OutgoingAccount\\PhpMailConfig","@DATA":{"PhpMail":true}}',
        ], ['1' => '1']);

        if ($input->getOption('with-safe-emails')) {
            $output->writeln('Making emails safe by appending .xxx');
            $test = $db->fetchColumn('SELECT email FROM people_emails LIMIT 1');
            if (!strpos($test, '.xxx')) {
                $db->executeUpdate("UPDATE people_emails SET email = CONCAT(email, '.xxx')");
            }
        }

        if ($input->getOption('with-password')) {
            $db->update('people', ['password' => '$2a$11$83Lh30c5D3ZasoQ1D5AFZOLfL4zBBzF6RZ0X7oTGVeI34Tqhu3U7S', 'password_scheme' => 'bcrypt'], ['1' => '1']);
        }

        if ($input->getOption('with-clean')) {
            $db->exec('SET FOREIGN_KEY_CHECKS = 0');
            foreach ([
                'agent_activity',
                'agent_alerts',
                'api_key_log',
                'api_key_rate_limit',
                'api_log',
                'api_token_rate_limit',
                'audit_logs',
                'chat_conversation_pings',
                'chat_round_robin_log',
                'drafts',
                'email_uids',
                'hit_record',
                'jobs',
                'log_items',
                'login_log',
                'page_view_log',
                'password_history',
                'rate_limit_log',
                'result_cache',
                'round_robin_log',
                'searchlog',
                'sess_data',
                'sessions',
                'snippet_use_log',
                'system_alerts_events',
                'system_alerts_incident_events',
                'system_alerts_incidents',
                'system_alerts_incidents',
                'usersource_sync_log',
            ] as $t) {
                $output->writeln("Cleaning table $t");
                $db->exec("TRUNCATE TABLE $t");
            }
            $db->exec('SET FOREIGN_KEY_CHECKS = 1');
        }

        return 0;
    }

    private function hasNonDeskproNotifs()
    {
        $globalSettings = $this->getContainer()->get('settings_resolver')->getGlobalSettings();

        $strategiesConfig = $globalSettings->get('notification.settings.strategies');
        $strategies       = array_merge(
            is_array($strategiesConfig) ? $strategiesConfig : [],
            [$globalSettings->get('notification.settings.default_strategy')]
        );

        foreach ($strategies as $strategy) {
            foreach ($strategy['delivery'] as $handler) {
                if ($handler !== 'db') {
                    return true;
                }
            }
        }

        return false;
    }
}
