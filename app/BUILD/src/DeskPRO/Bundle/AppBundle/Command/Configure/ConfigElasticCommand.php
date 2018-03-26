<?php

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
