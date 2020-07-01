<?php

namespace DeskPRO\Bundle\DevBundle\Command;

use DpRun\DpEnv;
use DpSys\Boot\BootTask\HttpKernelBootTask;
use DpSys\Kernel\BaseKernel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;
use Twig\Error\SyntaxError;
use Twig\Token;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Class TwigExtensionsInUseCommand
 *
 * @package DeskPRO\Bundle\UpdateBundle\Command\Dev
 */
class TwigExtensionsInUseCommand extends ContainerAwareCommand
{
    const KERNEL_INSTANCE_IDS = [
        'messenger',
        'apiv2',
        'user',
        'dp',
    ];

    const TWIG_SERVICE_IDS = [
        'twig',
        'templating.email.twig',
        'templating.new_email.twig',
    ];

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('dpdev:twig-extensions-in-use')
            ->addArgument(
                'output_dir',
                InputArgument::REQUIRED,
                'Report output directory',
                null
            )
            ->addOption(
                'base-scan-dir',
                'b',
                InputOption::VALUE_OPTIONAL,
                'Base directory to scan from',
                realpath(__DIR__.'/../../../../')
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        error_reporting(E_ERROR | E_WARNING | E_PARSE);

        $outputDir = rtrim(getcwd().DIRECTORY_SEPARATOR.$input->getArgument('output_dir'), DIRECTORY_SEPARATOR);

        $output->writeln("Analysing...");

        $filters   = [];
        $functions = [];

        foreach (self::KERNEL_INSTANCE_IDS as $id) {
            $container = $this->getContainerForKernel($id);

            foreach (self::TWIG_SERVICE_IDS as $twigServiceId) {
                if (!$container->has($twigServiceId)) {
                    continue;
                }

                $tokenNames = $this->getTwigExtensionTokenShortNames($container->get($twigServiceId));

                $filters   = array_merge($filters, $tokenNames['filters']);
                $functions = array_merge($functions, $tokenNames['functions']);
            }
        }

        $filters = array_fill_keys($filters, 0);
        $functions = array_fill_keys($functions, 0);

        /** @var Environment $twig */
        $twig = $this->getContainer()->get('twig');

        $finder = (new Finder())
            ->in($input->getOption('base-scan-dir'))
            ->files()
            ->name('*.twig')
        ;

        $countsPerFileOutput = [];

        /** @var \SplFileInfo $file */
        foreach ($finder as $filepath => $file) {
            $stream = $twig->tokenize(file_get_contents($filepath));
            $names  = [];

            try {
                while ($token = $stream->next()) {
                    if ($token->getType() === Token::NAME_TYPE) {
                        $names[] = trim($token->getValue());
                    }
                }
            } catch (SyntaxError $e) {
            }

            if (empty($names)) {
                continue;
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

        $output->writeln("Generating Reports...");

        $countPerFileReportFile = $outputDir.DIRECTORY_SEPARATOR."name_count_per_file.csv";
        $countPerNameReportFile = $outputDir.DIRECTORY_SEPARATOR."name_count.csv";

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

    /**
     * @param Environment $twig
     * @return array[]
     * @throws \Exception
     */
    private function getTwigExtensionTokenShortNames(Environment $twig)
    {
        $filters   = [];
        $functions = [];

        foreach ($twig->getExtensions() as $extension) {
            foreach ($extension->getFilters() as $name => $filter) {
                if ($filter instanceof TwigFilter) {
                    $filters[$filter->getName()] = $filter->getName();
                } elseif ($filter instanceof \Twig_Filter_Method) {
                    $filters[$name] = $name;
                } else {
                    throw new \Exception("Failed to determine filter name from ".get_class($filter));
                }
            }

            foreach ($extension->getFunctions() as $name => $function) {
                if ($function instanceof TwigFunction) {
                    $functions[$function->getName()] = $function->getName();
                } else {
                    throw new \Exception("Failed to determine function name from ".get_class($function));
                }
            }
        }

        return [
            'filters'   => $filters,
            'functions' => $functions,
        ];
    }

    /**
     * @param string $instanceId
     * @return ContainerInterface|null
     */
    private function getContainerForKernel($instanceId)
    {
        $env = new DpEnv(__DIR__.'/../../../../../../../');

        /** @var BaseKernel $kernel */
        list ('http_kernel' => $kernel) = (new HttpKernelBootTask())
            ->run($env, ['interface_id' => $instanceId, 'request' => new Request()])
        ;

        $kernel->boot();

        return $kernel->getContainer();
    }
}
