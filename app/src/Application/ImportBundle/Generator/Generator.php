<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * @package Generator
 */

namespace Application\ImportBundle\Generator;

use Application\ImportBundle\Generator\Plugin\GeneratorPluginInterface;
use Orb\Util\OptionsArray;
use Symfony\Component\Console\Input\InputInterface;
use Exception;

/**
 * Class Generator
 * @package Application\ImportBundle\Generator
 */
class Generator extends AbstractGenerator implements GeneratorInterface, LoggerAwareInterface
{
    /**
     * @var array
     */
    private $plugins = array();

    /**
     * @param InputInterface $input
     * @return GeneratorConfig
     */
    public function createGeneratorConfig(InputInterface $input)
    {
        $config = new GeneratorConfig();

        $import_config = new OptionsArray(dp_get_config('import', array()));
        $config->setOutputPath($import_config->get('output_path'));
        $config->setLogPath($import_config->get('log_path', dp_get_log_dir() . '/export'));
        $config->mode = $import_config->get('mode', 'test');
        $config->mark_done = $import_config->get('mark_done', true);

        if ($input->hasArgument('script')) {
            $config->setType($input->getArgument('script'));
        }
        if ($input->hasOption('output-path')) {
            $config->setOutputPath(rtrim($input->getOption('output-path'), "\\/") . "/");
        }
        if ($input->hasOption('input-path')) {
            $config->setInputPath($input->getOption('input-path'));
        }
        if ($input->hasOption('log-path')) {
            $config->setLogPath($input->getOption('log-path'));
        }
        if ($input->hasOption('mode')) {
            $config->mode = $input->getOption('mode');
        }
        if ($input->hasOption('live')) {
            $config->mode = 'live';
        }
        if ($input->hasOption('mark-done')) {
            $config->mark_done = (bool)$input->getOption('mark-done');
        }

        if (!is_dir($config->getOutputPath())) {
            throw new \InvalidArgumentException(sprintf(
                    'Invalid configuration: data_path is invalid (got %s)',
                    $config->getOutputPath())
            );
        }

        return $config;
    }

    /**
     * Attach a generator
     *
     * @param GeneratorPluginInterface $plugin
     * @return $this
     */
    public function addPlugin(GeneratorPluginInterface $plugin)
    {
        $this->plugins[] = $plugin;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function generateJson()
    {
        if (!$this->config) {
            throw new \Exception('Generator configuration is not set up');
        }
        if ($this->config->mode == 'live') {
            foreach (array('people', 'tickets') as $n) {
                if (!is_dir($this->config->getOutputPath() . $n)) {
                    mkdir($this->config->getOutputPath() . $n, 0777, true);
                }
            }
        }

        $this->getPlugin()->generateJson();
    }

    /**
     * Get generator plugin by configuration
     *
     * @return GeneratorPluginInterface
     * @throws Exception
     */
    private function getPlugin()
    {
        if (!$this->config) {
            throw new \Exception('Generator configuration is not set up');
        }

        foreach ($this->plugins as $plugin) {
            /** @var GeneratorPluginInterface $plugin */
            if ($plugin->getType() === $this->config->getType()) {
                $plugin->setConfig($this->config);

                if ($this->logger && $plugin instanceof LoggerAwareInterface) {
                    /** @var LoggerAwareInterface $plugin */
                    $plugin->setLogger($this->logger);
                }

                return $plugin;
            }
        }

        throw new \Exception(sprintf('Generator `%s` not found', $this->config->getType()));
    }
}
