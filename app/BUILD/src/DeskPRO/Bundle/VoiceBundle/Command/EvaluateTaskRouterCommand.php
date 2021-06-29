<?php

namespace DeskPRO\Bundle\VoiceBundle\Command;

use DeskPRO\Bundle\AppBundle\Util\HttpClient;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class EvaluateTaskRouterCommand.
 */
class EvaluateTaskRouterCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dp:voice:evaluate-task-router');
        $this->addOption('base_url', null, InputOption::VALUE_REQUIRED);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client  = new HttpClient();
        $baseUrl = $input->getOption('base_url') ?: $this->getContainer()->get('settings_resolver')->getGlobalSettings()->get('core.deskpro_url');
        $baseUrl = rtrim($baseUrl, '/');

        while (1) {
            $client->request('GET', $baseUrl.'/api/v2/task_router/evaluate');
            sleep(1);
        }
    }
}
