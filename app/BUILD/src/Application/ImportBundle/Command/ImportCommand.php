<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace Application\ImportBundle\Command;

use DeskPRO\ImporterTools\Helpers\WriteHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ImportCommand.
 */
class ImportCommand extends AbstractImporterCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('import')
            ->setHelp('Gets data from the external source.')
            ->addArgument('file', InputArgument::REQUIRED, 'Exporter script name')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setLoggerHandlers($input, $output);

        $appEnv   = $this->getContainer()->get('deskpro.app_env');
        $filename = $input->getArgument('file');
        $basePath = realpath($appEnv->getDpRoot().'/bin/deskpro-importer-tools');
        if (!$basePath) {
            $basePath = realpath($appEnv->getDpRoot().'/bin/deskpro-importer-tools-master');
        }

        // register importer inc files in autoload
        $importerAutoload = $basePath.'/inc/autoload.php';
        if (!file_exists($importerAutoload)) {
            throw new \Exception('Unable to locate importer tools autoload');
        }

        require_once $importerAutoload;

        // resolve importer script
        if ($filename === 'custom') {
            $script = $basePath.'/custom/custom.php';
        } else {
            $script = $basePath.'/importers/'.$filename.'/'.$filename.'.php';
        }

        if (!file_exists($script)) {
            throw new \Exception("File $script not found");
        }

        global $DP_CONTAINER;
        $DP_CONTAINER = $this->getContainer();

        $writer = WriteHelper::getHelper();
        $writer->setOutputPath($this->getImporterDefaultOutputPath());

        // register the script lib files in autoload
        $scriptLibPath = dirname($script);
        if (file_exists($scriptLibPath.'/lib/autoload.php')) {
            require_once $scriptLibPath.'/lib/autoload.php';
        } elseif (file_exists($scriptLibPath.'/lib/vendor/autoload.php')) {
            require_once $scriptLibPath.'/lib/vendor/autoload.php';
        }

        require_once $script;
    }
}
