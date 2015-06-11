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

use Application\ImportBundle\Generator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Import command
 * Read and parse an external data and import it to database
 *
 * Class ImportCommand
 * @package Application\ImportBundle\Command
 */
class ImportCommand extends AbstractGenerateCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('dp:import:run');
        $this->setHelp("Executes the importer.");
        $this->addOption('batch', 'b', InputOption::VALUE_NONE, 'Runs only the next batch');

        parent::configure();
    }

    /**
     * {@inheritDoc}
     */
    protected function doExecute(Generator\GeneratorConfig $config, LoggerInterface $logger, InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('batch')) {
            $config->setWriterType(Generator\Writer\WriterInterface::TYPE_DESK_PRO);
            $generator = $this->createGenerator($config, $logger);

            $this->createAndSetProgressBar($generator, $output);
            $this->generate($generator, $output, $logger);


        } else {
            if ($r = $this->checkPhp($input, $output) !== 0) {
                return $r;
            }

            do {
                $rerun = false;
                $cmd = sprintf('php cmd.php dp:import:run %s --output-path %s -b --verbose', escapeshellarg($input->getArgument('script')), escapeshellarg($input->getOption('output-path')));
                $proc = new Process($cmd, realpath(DP_ROOT.'/../'));
                $proc->setTimeout(18000);
                $proc->run(function($type, $data) use ($output) {
                    $output->write($data);
                });

                $config = $this->createGeneratorConfig($input, $this->getSupportedEntityTypes());
                $generator = $this->createGenerator($config, $logger);

                if ($generator instanceof Generator\Exporter\ExporterBatchInterface) {
                    $rerun = $generator->getDefaultBatchConfig()->getHasRemaining();
                }
            } while($rerun);
        }
    }

    private function checkPhp(InputInterface $input, OutputInterface $output)
    {
        #-------------------------
        # Check PHP infos
        #-------------------------

        if (dp_is_php_path_guessed()) {
            $cmd = sprintf(
                "%s %s",
                dp_get_php_path(),
                escapeshellarg(DP_ROOT.'/bin/phpinfo.php')
            );

            $ret = null;
            $out = null;
            exec($cmd, $out, $ret);

            $fail = true;
            if ($out) {
                $check_phpinfo = implode("\n", $out);
                $fail = !\Orb\Util\Env::isSamePhpInfo(
                    \Orb\Util\Env::getPhpInfo(),
                    $check_phpinfo
                );
            }

            if ($fail) {
                $output->write('<error>Could not find path to PHP (Detected PHP appears different than running PHP)</error>');
                $output->write('<error>Specify path to PHP in config.php by setting the $DP_CONFIG[\'php_path\'] option.</error>');
                return 1;
            }
        }

        #-------------------------
        # Make sure PHP we have passes requirements
        #-------------------------

        $cmd = sprintf(
            "%s %s",
            dp_get_php_path(),
            escapeshellarg(DP_ROOT.'/bin/check-req.php')
        );

        $ret = null;
        $out = null;
        exec($cmd, $out, $ret);

        if (!$out) $out = array();

        $out = implode("\n", $out);

        if ($ret || strpos($out, 'OKAY') === false) {
            $output->write('<error>PHP sub-command binary fails server checks: ' . $out . '</error>');
            $output->write('<error>Check your config.php file to make sure $DP_CONFIG[\'php_path\'] is set to the correct PHP path.</error>');
            return 1;
        }

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function checkConfiguration(Generator\GeneratorConfig $config)
    {
        if ($config->needInputPath() && ! $config->getInputPath()) {
            throw new RuntimeException('Input path must be specified');
        }
        if ($config->isBatchExporter() && ! $config->getOutputPath() && ! $config->getInputPath()) {
            switch ($config->getExporterType()) {
                case Generator\Exporter\ExporterInterface::TYPE_ZENDESK:
                case Generator\Exporter\ExporterInterface::TYPE_OS_TICKET:
                    throw new RuntimeException(sprintf(
                        'Output path must be specified for batch exporter `%s`',
                        $config->getExporterType()
                    ));
                case Generator\Exporter\ExporterInterface::TYPE_JSON:
                case Generator\Exporter\ExporterInterface::TYPE_CSV:
                    throw new RuntimeException(sprintf(
                        'Input path must be specified for batch exporter `%s`',
                        $config->getExporterType()
                    ));
            }
        }
    }
}
