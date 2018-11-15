<?php

namespace DeskPRO\Bundle\DevBundle\Command\Lang;

use DeskPRO\Bundle\DevBundle\Language\PhrasesFinder;
use DeskPRO\Component\Util\ListUtils;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class CheckMissingCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dpdev:lang:check-missing')
            ->setDescription('Look for phrases missing from the translation files.')
            ->addOption(
                'zone',
                'z',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Specify the zone as comma-sep list: adm, admin, agent, api, portal, user',
                ['all']
            )
            ->addOption(
                'ignore-zone',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Specify the zones to IGNORE as comma-sep list: adm, admin, agent, api, portal, user',
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
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $env      = $this->getContainer()->get('deskpro.app_env');
        $lang_dir = $env->getAppDir().'/locales/en-US';

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
                if ($f->isDir()) {
                    $zones[] = $f->getBasename();
                }
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

        $finder = Finder::create()->files()->name('*.php');
        foreach ($zones as $z) {
            $finder->in($lang_dir.'/'.$z);
        }

        $phrase_ids = [];
        foreach ($finder as $f) {
            $tmp        = require $f->getRealPath();
            $phrase_ids = array_merge($phrase_ids, array_keys($tmp));
        }
        unset($tmp);

        $prefixes = [];
        foreach ($phrase_ids as $phrase_id) {
            $prefix            = preg_replace('/\.[^\.]+$/', '.', $phrase_id);
            $prefixes[$prefix] = $prefix;
        }

        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln(sprintf('Found %d prefixes', count($prefixes)));
        }

        //------------------------------
        // Find uses
        //------------------------------

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

        $pfinder = new PhrasesFinder($env->getAppDir(), $prefixes, 1, $types);

        if ($input->getOption('include-dynamic')) {
            $output->writeln('Including known dynamic phrases');
            $pfinder->includeKnownDynamic();
        }

        $phrases_found = $pfinder->getPhrasesByPrefix();

        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln(sprintf('Found %d phrases matching prefixes', count($phrases_found)));
        }

        //------------------------------
        // Prepare output
        //------------------------------

        $cols = ['MissingPhrases'];
        $data = array_diff($phrases_found, $phrase_ids);

        //------------------------------
        // Output
        //------------------------------

        switch ($format) {
            case 'table':
                /** @var \Symfony\Component\Console\Helper\TableHelper $table */
                $table = $this->getHelper('table');
                $table->setHeaders($cols);
                foreach ($data as $row) {
                    $table->addRow([$row]);
                }
                $table->addRow(['TOTAL: '.count($data)]);
                $table->render($output);
                break;

            case 'json':
                echo json_encode($data, JSON_PRETTY_PRINT);
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
