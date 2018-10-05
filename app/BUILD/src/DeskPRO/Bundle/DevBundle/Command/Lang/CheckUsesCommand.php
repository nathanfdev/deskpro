<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\DevBundle\Command\Lang;

use DeskPRO\Bundle\DevBundle\Language\PhrasesFinder;
use DeskPRO\Component\Util\ListUtils;
use DeskPRO\Component\Util\MapUtils;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckUsesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dpdev:lang:check-uses')
            ->setDescription(
                'Checks all phrases. Use this to see context (where a phrase is used) or to find missing phrases.'
            )
            ->addOption(
                'zone',
                'z',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Specify the zone as comma-sep list: admin, agent, api, portal',
                ['all']
            )
            ->addOption(
                'ignore-zone',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Specify the zones to IGNORE as comma-sep list: admin, agent, api, portal',
                []
            )
            ->addOption(
                'format',
                'o',
                InputOption::VALUE_REQUIRED,
                'Output format: table, json, csv',
                'table'
            )
            ->addOption(
                'filetype',
                't',
                InputOption::VALUE_REQUIRED,
                'Scan which files? js, php or twig or all',
                'all'
            )
            ->addOption(
                'include-dynamic',
                null,
                InputOption::VALUE_NONE,
                'Attempt to find phrases that we know are used dynamically and have no explicit usage'
            )
            ->addArgument(
                'report',
                InputArgument::OPTIONAL,
                'Report mode: "context" to show all found uses, or "missing" to only report phrases where we could not find a use.',
                'missing'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $env      = $this->getContainer()->get('deskpro.app_env');
        $lang_dir = $env->getAppDir().'/locales/en-US';

        $report = $input->getArgument('report');
        if ($report !== 'context' && $report !== 'missing') {
            $output->writeln('<error>report must be: context or missing</error>');

            return 1;
        }

        $format = $input->getOption('format');
        if ($format !== 'table' && $format !== 'json' && $format !== 'csv') {
            $output->writeln('<error>format must be: table, json or csv</error>');

            return 1;
        }

        //------------------------------
        // Decide which zones to work on
        //------------------------------

        $zones        = $input->getOption('zone');
        $ignore_zones = $input->getOption('ignore-zone');

        if (in_array('all', $zones)) {
            $zones = [];
            $iter  = new \FilesystemIterator($lang_dir);
            /** @var \SplFileInfo $f */
            foreach ($iter as $f) {
                $zones[] = $f->getBasename('.php');
            }
        }

        if ($ignore_zones) {
            $zones = ListUtils::filter($zones, function ($v) use ($ignore_zones) {
                return !in_array($v, $ignore_zones);
            });
        }

        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln('Finding phrases in zones: '.implode(', ', $zones));
        }

        //------------------------------
        // Load phrase IDs
        //------------------------------

        $finder = [];
        foreach ($zones as $z) {
            $zFile = $lang_dir.DIRECTORY_SEPARATOR.$z.'.php';
            if (file_exists($zFile)) {
                $finder[] = new \SplFileInfo($zFile);
            }
        }

        $phrase_ids = [];
        foreach ($finder as $f) {
            $tmp        = require $f->getPathname();
            $phrase_ids = array_merge($phrase_ids, array_keys($tmp));
        }
        unset($tmp);

        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln(sprintf('Finding uses for %d phrases', count($phrase_ids)));
        }

        //------------------------------
        // Find uses
        //------------------------------

        if ($report === 'missing') {
            $limit = 1;
        } else {
            $limit = 0;
        }

        $types = [];
        switch ($input->getOption('filetype')) {
            case 'php':
                $types[] = 'php';
                break;
            case 'twig':
                $types[] = 'twig';
                break;
            case 'js':
                $types[] = 'js';
                break;
            case 'all':
                $types[] = 'twig';
                $types[] = 'php';
                $types[] = 'js';
                break;
            default:
                $output->writeln(
                    "<error>Invalid --filetype param. Must be either 'js', 'php' or 'twig' or 'all'.</error>"
                );

                return 1;
        }

        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln(sprintf('Finding uses in files: %s', implode(', ', $types)));
        }

        $pfinder = new PhrasesFinder($env->getAppDir(), $phrase_ids, $limit, $types);

        if ($input->getOption('include-dynamic')) {
            $output->writeln('Including known dynamic phrases');
            $pfinder->includeKnownDynamic();
        }

        $use_info = $pfinder->getUseInfo();

        //------------------------------
        // Prepare output
        //------------------------------

        if ($report === 'missing') {
            $cols = ['unusedPhraseId'];
            $data = MapUtils::mapToList($use_info['phrase_counts'], function ($phrase_id, $count) {
                if ($count === 0) {
                    return [$phrase_id];
                } else {
                    return;
                }
            });
            $data = ListUtils::filterOutFalsey($data);
        } else {
            $cols = ['phraseId', 'templateNames'];
            $data = MapUtils::mapToList($use_info['phrase_uses'], function ($phrase_id, $tpls) {
                return [
                    $phrase_id,
                    $tpls,
                ];
            });
        }

        //------------------------------
        // Output
        //------------------------------

        switch ($format) {
            case 'table':
                /** @var \Symfony\Component\Console\Helper\TableHelper $table */
                $table = $this->getHelper('table');
                $table->setHeaders($cols);
                foreach ($data as $row) {
                    $row = array_map(function ($r) {
                        if (is_array($r)) {
                            return implode("\n", $r);
                        }

                        return $r;
                    }, $row);
                    $table->addRow($row);
                }
                $table->addRow(['TOTAL: '.count($data)]);
                $table->render($output);
                break;

            case 'json':
                $json_data = array_map(function ($x) use ($cols) {
                    $row = [];
                    foreach ($cols as $idx => $key) {
                        $row[$key] = $x[$idx];
                    }

                    return $row;
                }, $data);
                echo json_encode($json_data, JSON_PRETTY_PRINT);
                break;

            case 'csv':
                fputcsv(STDOUT, $cols);
                foreach ($data as $row) {
                    $row = array_map(function ($r) {
                        if (is_array($r)) {
                            return implode(', ', $r);
                        }

                        return $r;
                    }, $row);
                    fputcsv(STDOUT, $row);
                }
                break;
        }

        return 0;
    }
}
