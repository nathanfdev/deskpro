<?php

namespace DeskPRO\Bundle\VoiceBundle\Command;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\TwilioVoiceAccount;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ReconfigureVoiceNumbersCommand.
 */
class ReconfigureVoiceNumbersCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dp:voice:reconfigure-voice-numbers');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        // if this account has proxy settings, get proxy urls from members area
        // and reload the voice proxy settings
        if ($this->getContainer()->get('voice_settings_resolver')->getTwilioProxyApiUrl()) {
            // we need to get a random admin
            // to fetch data from members area
            $person = $em->getRepository(Person::class)->findOneBy([
                'is_agent'   => 1,
                'can_admin'  => 1,
                'is_deleted' => 0,
            ]);

            if (!$person) {
                $output->writeln('Unable to get an admin, break the sync process');

                return 1;
            }

            try {
                $proxySettings = $this->getContainer()->get('dp.voice.proxy')->loadTwilioProxySettings($person, true);
            } catch (\Exception $e) {
                $output->writeln('Unable to load proxy settings, break the sync process');

                return 1;
            }

            if ($proxySettings) {
                $this->getContainer()->get('dp.voice.proxy')->createTwilioProxyAccount(
                    $proxySettings['twilioProxyServiceUrl'],
                    $proxySettings['accessToken'],
                    $proxySettings['authToken']
                );
            }
        }

        // update callback urls in twilio
        $accounts = $em->getRepository(TwilioVoiceAccount::class)->findAll();
        foreach ($accounts as $account) {
            $this->getContainer()->get('dp.voice.twiml_app_configurator')->createOrUpdateTwimlApp($account);
        }

        return 0;
    }
}
