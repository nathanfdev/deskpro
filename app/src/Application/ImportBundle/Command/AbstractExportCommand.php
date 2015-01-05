<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

namespace Application\ImportBundle\Command;

use Application\ImportBundle\Generator\GeneratorConfig;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Orb\Util\OptionsArray;

/**
 * Class AbstractExportCommand
 * @package Application\ImportBundle\Command
 */
abstract class AbstractExportCommand extends ContainerAwareCommand
{
    /**
     * Creates a new generator config instance
     *
     * @param InputInterface $input
     * @return GeneratorConfig
     */
    public function createGeneratorConfig(InputInterface $input)
    {
        $config = new GeneratorConfig();
        $import_config = new OptionsArray(dp_get_config('import', array()));
        $config
            ->setOutputPath($import_config->get('output_path'))
            ->setLogPath($import_config->get('log_path', dp_get_log_dir() . '/export'))
            ->setMode($import_config->get('mode', GeneratorConfig::MODE_TEST))
            ->setMarkDone($import_config->get('mark_done', true));

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
            $config->setMode($input->getOption('mode'));
        }
        if ($input->hasOption('live')) {
            $config->setMode(GeneratorConfig::MODE_LIVE);
        }
        if ($input->hasOption('mark-done')) {
            $config->setMarkDone($input->getOption('mark-done'));
        }

        return $config;
    }
}
