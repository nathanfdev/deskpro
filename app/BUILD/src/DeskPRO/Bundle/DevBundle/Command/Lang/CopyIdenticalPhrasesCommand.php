<?php

namespace DeskPRO\Bundle\DevBundle\Command\Lang;

use DeskPRO\Bundle\DevBundle\Language\CopyIdenticalPhrases;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class CopyIdenticalPhrasesCommand
 *
 * @package DeskPRO\Bundle\DevBundle\Command\Lang
 */
class CopyIdenticalPhrasesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('dpdev:lang:copy-identical-phrases')
            ->setDescription('Copy any unchanged phrases over to another phrase ID')
            ->addOption(
                'locales-dir',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Location of the locales directory',
                null
            )
            ->addOption(
                'root-phrase-id-prefix',
                'i',
                InputOption::VALUE_OPTIONAL,
                'The root phrase ID prefix, e.g. "helpcenter"',
                'helpcenter'
            )
            ->addOption(
                'root-language',
                'l',
                InputOption::VALUE_OPTIONAL,
                'The root language, e.g. "en-US" (corresponds to the locale subdirectory)',
                'en-US'
            )
            ->addOption(
                'language-files',
                'f',
                InputOption::VALUE_OPTIONAL,
                'A comma separated list of target language files, e.g. "user.yml,backend.yml"',
                'user.yml'
            )
            ->addOption(
                'prepend',
                'p',
                InputOption::VALUE_NONE,
                'Prepends the new ID prefixed translations to the other language files'
            )
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $kernelRootDir = $this->getContainer()->getParameter('kernel.root_dir');

        $isPrepend     = $input->getOption('prepend');
        $idPrefix      = $input->getOption('root-phrase-id-prefix');
        $localesDir    = $input->getOption('locales-dir') ?: $kernelRootDir.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'locales';
        $rootLanguage  = $input->getOption('root-language');
        $languageFiles = array_map('trim', explode(',', $input->getOption('language-files')));

        $output->writeln("Analysing...");

        $same = $this->getCopyIdenticalPhrasesService()->analyze(
            $localesDir,
            $rootLanguage,
            $languageFiles,
            $idPrefix
        );

        foreach ($same as $languageFile => $replacementTuple) {
            $output->writeln("Replacements for <info>{$languageFile}</info>:");

            /** @var \Symfony\Component\Console\Helper\TableHelper $table */
            $table = $this->getHelper('table');
            $table->setHeaders(['Candidate', 'Value', 'Hash']);
            foreach ($replacementTuple as $hash => $replacement) {
                list ($_, $addId, $value) = $replacement;
                $table->addRow([
                    $addId,
                    is_string($value) ? $this->truncateString($value) : print_r($value, true),
                    $hash,
                ]);
            }
            $table->render($output);
        }

        $output->writeln("Building replacements...");

        $additions = $this->getCopyIdenticalPhrasesService()->buildAdditions(
            $same,
            $localesDir,
            $rootLanguage
        );

        foreach ($additions as $languageFile => $phrases) {
            $output->writeln("Additions for <info>{$languageFile}</info>:");
            $output->writeln("---------");
            $output->write(Yaml::dump($phrases));
            $output->writeln("---------");
        }

        if ($isPrepend) {
            $output->writeln("Prepending to language files...");
            $output->writeln("---------");

            $this->getCopyIdenticalPhrasesService()->prependAdditions(
                $additions,
                function ($languageFile) use ($output) {
                    $output->writeln(" + Prepended to <info>{$languageFile}</info>");
                }
            );

            $output->writeln("---------");
        }

        $output->writeln("Done.");

    }

    /**
     * @return CopyIdenticalPhrases
     */
    private function getCopyIdenticalPhrasesService()
    {
        return $this->getContainer()->get('dpdev.language.copy_identical_phrases');
    }

    /**
     * @param string $string
     * @param int $length
     * @return string
     */
    private function truncateString($string, $length = 30)
    {
        if (strlen($string) <= $length) {
            return $string;
        }

        return substr($string, 0, $length).'...';
    }
}
