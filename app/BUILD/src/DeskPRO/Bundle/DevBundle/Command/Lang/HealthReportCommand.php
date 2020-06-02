<?php

namespace DeskPRO\Bundle\DevBundle\Command\Lang;

use DeskPRO\Bundle\DevBundle\Language\PhrasesFinder;
use DeskPRO\Component\Util\ListUtils;
use DeskPRO\Component\Util\MapUtils;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class HealthReportCommand extends ContainerAwareCommand
{
    public static $knownPhraseIdExceptions = [
        'helpcenter.duration_short.days'      => ['uppercase-only'],
        'helpcenter.duration_short.months'    => ['uppercase-only'],
        'helpcenter.duration_short.years'     => ['uppercase-only'],
        'helpcenter.duration_short.weeks'     => ['uppercase-only'],
        'helpcenter.general.cc'               => ['uppercase-only'],
        'helpcenter.approvals.list_column_id' => ['uppercase-only'],
        'helpcenter.general.eula'             => ['uppercase-only'],
        'helpcenter.tickets.view_btn_add_cc'  => ['uppercase-only'],
    ];

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dpdev:lang:health-report')
            ->setDescription('Generates a report file with phrase usage')
            ->addOption('with-usage', null, InputOption::VALUE_OPTIONAL, 'Include usage report. Optionally with a base URL to link to files (e.g. github).')
            ->addArgument('file', InputArgument::REQUIRED, 'The file to scan. E.g. helpcenter.yml');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $langFile = $this->getContainer()->get('deskpro.low_dp_env')->getAppDir()
            .DIRECTORY_SEPARATOR
            .'locales'
            .DIRECTORY_SEPARATOR
            .'en-US'
            .DIRECTORY_SEPARATOR
            .$input->getArgument('file');

        if (!file_exists($langFile)) {
            $output->writeln('<error>Invalid file</error>');

            return 1;
        }

        $phrases = Yaml::parse(file_get_contents($langFile));
        ksort($phrases);

        //-----------------------------------------
        // Keys
        //-----------------------------------------

        $badKeys = [];
        foreach (array_keys($phrases) as $keyId) {
            $probs = [];
            if (preg_match('/[A-Z]/', $keyId)) {
                $probs[] = 'uppercase-char';
            }

            if (strpos($keyId, '-')) {
                $probs[] = 'dash';
            }

            if (substr_count($keyId, '.') !== 2) {
                $probs[] = 'invalid-ns';
            }

            if ($probs) {
                $badKeys[$keyId] = $probs;
            }
        }

        //-----------------------------------------
        // Strings
        //-----------------------------------------

        $badStrings = [];
        foreach ($phrases as $keyId => $text) {
            $probs = [];
            if (strpos($text, 'class="') !== false || strpos($text, 'id="') !== false) {
                $probs[] = 'complex-html';
            }

            if (preg_match('/<(b|i|u)[^r]/', $text)) {
                $probs[] = 'legacy-html';
            }

            if (preg_match('/\{(date|time)\}/', $text)) {
                $probs[] = 'pre-rendered-datetime';
            }

            if (preg_match('/\{(person)\}/', $text)) {
                $probs[] = 'unqualified-variable';
            }

            if (preg_match('/\{\w+\.[\w\.]+\}/', $text)) {
                $probs[] = 'object-variable';
            }

            if (preg_match('/^[A-Z0-9\W]+$/', $text)) {
                $probs[] = 'uppercase-only';
            }

            if ($probs) {
                $badStrings[$keyId] = ['problems' => $probs, 'text' => $text];
            }
        }

        $badStrings = MapUtils::filter($badStrings, function ($keyId, $info) {
            if (!isset(self::$knownPhraseIdExceptions[$keyId])) {
                return true;
            }

            $probs = ListUtils::filterOutValues($info['problems'], self::$knownPhraseIdExceptions[$keyId]);

            return !empty($probs);
        });

        //-----------------------------------------
        // Similar strings
        //-----------------------------------------

        $similarPhrases = [];
        $foundKeys      = [];

        foreach ($phrases as $keyId => $text) {
            if (in_array($keyId, $foundKeys)) {
                continue;
            }

            $scanIds = array_filter(array_keys($phrases), function ($k) use ($keyId) {
                return $k !== $keyId && !isset($foundKeys[$k]);
            });

            $similarTo = [];
            foreach ($scanIds as $scanId) {
                $perc = null;
                similar_text($text, $phrases[$scanId], $perc);
                if ($perc > 85) {
                    $similarTo[] = $scanId;
                }
            }

            if ($similarTo) {
                $foundKeys = array_merge($foundKeys, $similarTo, [$keyId]);
                $s         = [$keyId => $text];
                foreach ($similarTo as $k) {
                    $s[$k] = $phrases[$k];
                }
                $similarPhrases[] = $s;
            }
        }

        //-----------------------------------------
        // Usage
        //-----------------------------------------

        $usages         = null;
        $usagesNotFound = null;
        if (($baseUrl = $input->getOption('with-usage')) || $input->hasOption('with-usage')) {
            if (!is_string($baseUrl)) {
                $baseUrl = 'https://github.com/deskpro/deskpro/tree/develop';
            }

            $baseUrl = rtrim($baseUrl, '/') ?: '';

            $env = $this->getContainer()->get('deskpro.low_dp_env');

            $pfinder   = new PhrasesFinder($env->getAppDir(), array_keys($phrases), 0, ['twig', 'php']);
            $usageInfo = $pfinder->getUseInfo(true);

            $usages = MapUtils::map($usageInfo['phrase_uses'], function ($keyId, $tpls) {
                return [$keyId, ListUtils::map($tpls, function ($tpl) {
                    return '/app/BUILD'.$tpl;
                })];
            });
            ksort($usages);
            $usagesNotFound = MapUtils::map($usageInfo['phrase_counts'], function ($keyId, $count) use ($phrases) {
                if ($count === 0) {
                    return [$keyId, $phrases[$keyId]];
                } else {
                    return [$keyId, null];
                }
            });
            $usagesNotFound = MapUtils::filterOutFalsey($usagesNotFound);
        }

        //-----------------------------------------
        // Render
        //-----------------------------------------

        ob_start();
        include __DIR__.'/HealthReport.html.tpl.php';
        ob_end_flush();

        return 0;
    }
}
