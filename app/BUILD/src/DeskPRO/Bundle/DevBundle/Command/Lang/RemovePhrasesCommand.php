<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\DevBundle\Command\Lang;

use Application\DeskPRO\Languages\LangPackInfo;
use DeskPRO\Bundle\DevBundle\Language\LangPhpFileCompiler;
use DeskPRO\Component\Util\ListUtils;
use Orb\Util\Strings;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class RemovePhrasesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dpdev:lang:remove-phrases')
            ->setDescription('Removes phrases from the default English lang file(s). Patterns may be: exact, /regex/ or glob*')
            ->addOption('only', 'o', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Comma-seperated list of patterns that must match')
            ->addOption('except', 'k', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Comma-seperated list of patterns that must not match')
            ->addOption('apply', 'x', InputOption::VALUE_NONE, 'Write changes to disk. Without this, it is just a preview mode')
            ->addArgument('deletePhrases', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'A comma-seperated list of patterns, or a @/path/to/file which includes phrases one per line.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $deletePhrases = $this->readPatternOptions($input->getArgument('deletePhrases'));
        $onlyPhrases   = $this->readPatternOptions($input->getOption('only') ?: []);
        $exceptPhrases = $this->readPatternOptions($input->getOption('except') ?: []);
        $apply         = $input->getOption('apply');

        if (!$deletePhrases) {
            $output->writeln('You must specify one or more phrases to delete');

            return 1;
        }

        $langFileCompiler = new LangPhpFileCompiler();

        $langPacks = new LangPackInfo();
        $engDir    = $langPacks->getLangDir().DIRECTORY_SEPARATOR.'default';

        /** @var \SplFileInfo[] $engFiles */
        $engFiles = Finder::create()->in($engDir)->files()->name('*.php');

        foreach ($engFiles as $f) {
            $phrases    = require $f->getRealPath();
            $newPhrases = [];
            foreach ($phrases as $phraseId => $p) {
                $doDelete = false;
                if ($this->anyMatch($phraseId, $deletePhrases)) {
                    $doDelete = true;

                    if ($onlyPhrases && !$this->anyMatch($phraseId, $onlyPhrases)) {
                        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                            $output->writeln(sprintf('Skipped: %s -- no match `only` list', $phraseId));
                        }
                        $doDelete = false;
                    }
                    if ($doDelete && $exceptPhrases && $this->anyMatch($phraseId, $exceptPhrases)) {
                        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                            $output->writeln(sprintf('Skipped: %s -- matched `except` list', $phraseId));
                        }
                        $doDelete = false;
                    }
                    if ($doDelete) {
                        $output->writeln('Match: '.$phraseId);
                    }
                }

                if (!$doDelete) {
                    $newPhrases[$phraseId] = $p;
                }
            }

            if ($apply && count($phrases) !== count($newPhrases)) {
                $phpCode = $langFileCompiler->compilePhpCode($newPhrases);
                file_put_contents($f->getRealPath(), $phpCode);
                $output->writeln(sprintf('%s: %d phrases removed', $f->getBasename(), count($phrases) - count($newPhrases)));
            }
        }
    }

    private function anyMatch($phraseId, array $patterns)
    {
        foreach ($patterns as $patternInfo) {
            list($type, $pattern) = $patternInfo;
            switch ($type) {
                case 'regex':
                    if (preg_match($pattern, $phraseId)) {
                        return true;
                    }
                    break;
                case 'glob':
                    if (Strings::isStarMatch($pattern, $phraseId)) {
                        return true;
                    }
                case 'exact':
                    if ($pattern === $phraseId) {
                        return true;
                    }
                    break;
            }
        }

        return false;
    }

    private function readPatternOptions(array $phrases)
    {
        // Read in @/files
        $phrases = array_map(function ($v) {
            $v = trim($v);
            if ($v[0] === '@') {
                $file = substr($v, 1);
                if (!file_exists($file)) {
                    echo "Could not read file: $file\n";
                    exit(1);
                }
                $v = file($file);
                $v = array_map('trim', $v);
            }

            return $v;
        }, $phrases);

        // Flatten array (e.g. from files being @included)
        $phrases = ListUtils::flatten($phrases);

        // Turn it into a simple structure that tells us which kind of phrase it is
        $phrases = array_map(function ($v) {
            if ($v[0] === '/' && substr($v, -1) === '/') {
                $v = ['regex', $v];
            } elseif (strpos($v, '*') !== false) {
                $v = ['glob', $v];
            } else {
                $v = ['exact', $v];
            }

            return $v;
        }, $phrases);

        return $phrases;
    }
}
