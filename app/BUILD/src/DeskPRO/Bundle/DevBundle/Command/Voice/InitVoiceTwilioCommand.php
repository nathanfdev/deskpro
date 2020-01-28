<?php

namespace DeskPRO\Bundle\DevBundle\Command\Voice;

use DeskPRO\Bundle\AppBundle\Entity\TwilioVoiceAccount;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InitVoiceCommand.
 */
class InitVoiceTwilioCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('dpdev:voice:twilio-init')
            ->addOption('account_id', null, InputOption::VALUE_REQUIRED, 'Account SID, if it is a proxy account then use the proxy access token')
            ->addOption('auth_token', null, InputOption::VALUE_REQUIRED, 'Auth Token, if it is a proxy account then use the proxy auth token')
            ->addOption('public_url', null, InputOption::VALUE_REQUIRED, 'Public url (e.g. ngrok)')
            ->addOption('public_proxy_url', null, InputOption::VALUE_REQUIRED, 'Public proxy url from cloud services (e.g. ngrok)')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        // enable voice if it has not been enabled yet
        // init settings resolver first to be possible to use deskpro.feature_flags
        $settingsResolver = $container->get('settings_resolver');

        if (!$container->get('deskpro.feature_flags')->hasBeta('voice')) {
            $container->get('deskpro.toggle_feature_manager')->enableFeature('voice');
        }

        // set public url
        if (!$input->getOption('public_url')) {
            $output->writeln("<error>No public url was specified</error>");

            return 1;
        }

        $em = $container->get('doctrine.orm.entity_manager');

        // overwrite deskpro url with the public one
        $em->getConnection()->update(
            'settings',
            ['value' => $input->getOption('public_url')],
            ['name'  => 'core.deskpro_url']
        );

        $em->getConnection()->update(
            'settings_brand',
            ['value' => $input->getOption('public_url')],
            ['name'  => 'core.deskpro_url']
        );

        // reload settings because we need these settings
        // to create account app in voice account doctrine listener
        $settingsResolver->getGlobalSettings(true);

        if ($input->getOption('public_proxy_url')) {
            // configure as proxied account
            $container->get('dp.voice.cloud_proxy')->createTwilioProxyAccount(
                $input->getOption('public_proxy_url'),
                $input->getOption('account_id') ?: 'xxx-access-xxx',
                $input->getOption('auth_token') ?: 'xxx-auth-xxx'
            );
        } else {
            // configure as own account
            if (!$input->getOption('account_id')) {
                $output->writeln("<error>No account SID was specified</error>");

                return 1;
            }
            if (!$input->getOption('auth_token')) {
                $output->writeln("<error>No account auth token was specified</error>");

                return 1;
            }

            $account = new TwilioVoiceAccount();
            $account->setAccountId($input->getOption('account_id'));
            $account->setAuthToken($input->getOption('auth_token'));
            $account->setAccountName('Deskpro Voice Account');

            $em->persist($account);
            $em->flush();
        }

        return 0;
    }
}
