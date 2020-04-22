<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\DevBundle\Command\Lang;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class HealthReportCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dpdev:lang:health-report')
            ->setDescription('Generates a report file with phrase usage')
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

            if (preg_match('/<(b|i|u)/', $text)) {
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

        //-----------------------------------------
        // Similar strings
        //-----------------------------------------

        $similarPhrases = [];
        $foundKeys = [];

        foreach ($phrases as $keyId => $text) {
            if (isset($foundKeys[$keyId])) {
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
                $foundKeys = $foundKeys + $similarTo + [$keyId];
                $s = [$keyId => $text];
                foreach ($similarTo as $k) {
                    $s[$k] = $phrases[$k];
                }
                $similarPhrases[] = $s;
            }
        }

        //-----------------------------------------
        // Render
        //-----------------------------------------

        ob_start();
        include(__DIR__.'/HealthReport.html.tpl.php');
        ob_end_flush();

        return 0;
    }
}
