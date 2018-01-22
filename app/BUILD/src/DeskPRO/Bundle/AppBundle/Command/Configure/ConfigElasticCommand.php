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

namespace DeskPRO\Bundle\AppBundle\Command\Configure;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Class ConfigElasticCommand.
 */
class ConfigElasticCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('dp:config:elastic')
            ->addOption('no-reset', null, InputOption::VALUE_NONE, 'Do not automatically run dp:elastica:populate')
            ->setDescription('Enables Elastic search features and runs the initial populate command.')
            ->addArgument('elasticUrl', InputArgument::REQUIRED, 'The Elastic server URL like http://localhost:9200/ or specify OFF to turn Elastic off')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var \DpRun\DpEnv */
        global $DP_ENV;

        $rawUrl = $input->getArgument('elasticUrl');
        if ($rawUrl === 'OFF') {
            $this->setSetting('elastica.enabled', '0');
            $this->setSetting('elastica.clients.default.url', '');
            $output->writeln('<info>Elastic has been disabled.</info>');

            return 0;
        }

        $parts = parse_url($rawUrl);
        if (!$parts || empty($parts['host']) || empty($parts['scheme'])) {
            $output->writeln('<error>Please provide a URL to your Elastic server in the form: http://host:port/</error>');
        }

        if (empty($parts['path'])) {
            $parts['path'] = '/';
        }
        if (empty($parts['port'])) {
            $parts['port'] = $parts['scheme'] === 'https' ? '443' : '80';
        }

        if (!empty($parts['user'])) {
            $userPart = $parts['user'];
            if (!empty($parts['pass'])) {
                $userPart .= ':'.$parts['pass'];
            }
            $userPart .= '@';
        } else {
            $userPart = '';
        }

        $elasticUrl = $parts['scheme'].'://'.$userPart.$parts['host'].':'.$parts['port'].$parts['path'];

        $this->setSetting('elastica.enabled', '1');
        $this->setSetting('elastica.clients.default.url', $elasticUrl);
        $output->writeln('<info>Elastic has been enabled with the following URL: '.$elasticUrl.'</info>');

        if (!$input->getOption('no-reset')) {
            $output->writeln('Running initial indexing...');

            $proc = new Process(
                sprintf('"%s" bin/console dp:elastica:populate --reset', $DP_ENV->getConfig('paths.php_path')),
                $DP_ENV->getDpRoot()
            );
            $proc->setTimeout(3600);
            $proc->run(function ($type, $data) use ($output) {
                if ($type === 'out') {
                    $output->write($data);
                } else {
                    $output->write('<error>'.$data.'</error>');
                }
            });

            if (!$proc->isSuccessful()) {
                $output->writeln('<error>Populate failed with error status</error>');

                return 1;
            }
        }

        $output->writeln('<info>Done</info>');

        return 0;
    }

    /**
     * @param string $k
     * @param string $v
     *
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    private function setSetting($k, $v)
    {
        $db = $this->getContainer()->get('database_connection');
        $db->delete('settings', ['name' => $k]);
        $db->insert('settings', ['name' => $k, 'value' => $v]);
    }
}
