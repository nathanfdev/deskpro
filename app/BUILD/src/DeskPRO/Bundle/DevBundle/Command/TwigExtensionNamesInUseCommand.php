<?php

namespace DeskPRO\Bundle\DevBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Twig\Token;

/**
 * Class TwigExtensionNamesInUseCommand
 *
 * @package DeskPRO\Bundle\UpdateBundle\Command\Dev
 */
class TwigExtensionNamesInUseCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('dpdev:twig-extension-names-in-use')
            ->addArgument(
                'output_dir',
                InputArgument::REQUIRED,
                'Report output directory',
                null
            )
            ->addOption(
                'base-scan-dir',
                'b',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Base directory to scan from',
                null
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        error_reporting(E_ERROR | E_WARNING | E_PARSE);

        $outputDir = $input->getArgument('output_dir');
        $outputDir = rtrim(substr($outputDir, 0, 1) === DIRECTORY_SEPARATOR
            ? $outputDir
            : getcwd().DIRECTORY_SEPARATOR.$outputDir, DIRECTORY_SEPARATOR
        );

        $scanBaseDirs = $input->getOption('base-scan-dir')
            ? array_map(function ($dir) {
                return realpath(substr($dir, 0, 1) === DIRECTORY_SEPARATOR ? $dir : getcwd().DIRECTORY_SEPARATOR.$dir);
            }, $input->getOption('base-scan-dir'))
            : [realpath(__DIR__.'/../../../../')]
        ;

        $output->writeln("Analysing...");

        list ('filters' => $filters, 'functions' => $functions) = $this
            ->getContainer()
            ->get('dpdev.template.twig_extension_scanner')
            ->getAllExtensionTokenNames()
        ;

        $filters = array_fill_keys(array_keys(array_flip($filters)), 0);
        $functions = array_fill_keys(array_keys(array_flip($functions)), 0);

        $finder = (new Finder())
            ->in($scanBaseDirs)
            ->files()
            ->name('*.twig')
        ;

        $countsPerFileOutput = [];

        $this->getContainer()->get('dpdev.template.twig_template_parser')->parse(
            $finder,
            function ($tokens, \SplFileInfo $file) use ($output, &$filters, &$functions, &$countsPerFileOutput) {
                $names = isset($tokens[Token::NAME_TYPE])
                    ? $tokens[Token::NAME_TYPE]
                    : []
                ;

                if (empty($names)) {
                    return;
                }

                $realPath = str_replace(realpath(__DIR__.'/../../../../../../..'), '', $file->getRealPath());

                $output->writeln(" + {$realPath}");

                $filterCounts = [];
                foreach ($filters as $filter => &$count) {
                    $counts = array_count_values($names);
                    $count += isset($counts[$filter]) ? $counts[$filter] : 0;

                    if (in_array($filter, $names)) {
                        $filterCounts[$filter] = $counts[$filter];
                    }
                }

                foreach ($filterCounts as $name => $occurrences) {
                    if ($occurrences) {
                        $countsPerFileOutput[] = [
                            'name' => $name,
                            'type' => 'filter',
                            'occurrences' => $occurrences,
                            'file' => $realPath,
                        ];
                    }
                }

                $functionCounts = [];
                foreach ($functions as $function => &$count) {
                    $counts = array_count_values($names);
                    $count += isset($counts[$function]) ? $counts[$function] : 0;

                    if (in_array($function, $names)) {
                        $functionCounts[$function] = $counts[$function];
                    }
                }

                foreach ($functionCounts as $name => $occurrences) {
                    if ($occurrences) {
                        $countsPerFileOutput[] = [
                            'name' => $name,
                            'type' => 'function',
                            'occurrences' => $occurrences,
                            'file' => $realPath,
                        ];
                    }
                }
            }
        );

        $output->writeln("Generating Reports...");

        $countPerFileReportFile = $outputDir.DIRECTORY_SEPARATOR."name_count_per_file_".time().".csv";
        $countPerNameReportFile = $outputDir.DIRECTORY_SEPARATOR."name_count_".time().".csv";

        $fp = fopen($countPerFileReportFile, 'w');

        if (!empty($countsPerFileOutput)) {
            fputcsv($fp, array_map('strtoupper', array_keys($countsPerFileOutput[0])));
        }

        foreach ($countsPerFileOutput as $row) {
            fputcsv($fp, $row);
        }

        fclose($fp);

        $fp = fopen($countPerNameReportFile, 'w');

        fputcsv($fp, ['NAME', 'TYPE', 'OCCURRENCES']);

        foreach ($filters as $filter => $count) {
            fputcsv($fp, [$filter, 'filter', $count]);
        }

        foreach ($functions as $function => $count) {
            fputcsv($fp, [$function, 'function', $count]);
        }

        fclose($fp);

        $output->writeln(sprintf("<info> + %s</info>", realpath($countPerFileReportFile)));
        $output->writeln(sprintf("<info> + %s</info>", realpath($countPerNameReportFile)));

        $output->writeln('Done.');

        return 0;
    }
}
