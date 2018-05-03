<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\DevBundle\Command\Lang;

use Application\DeskPRO\Languages\LangPackInfo;
use DeskPRO\Bundle\DevBundle\Language\LangPhpFileCompiler;
use DeskPRO\Component\Util\ListUtils;
use DeskPRO\Component\Util\MapUtils;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class TrimExtraLangFilesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dpdev:lang:trim-extra-lang')
            ->setDescription('Removes lang keys from non-english files that do not exist in default.')
            ->addOption('apply', 'x', InputOption::VALUE_NONE, 'Write changes to disk. Without this, it is just a preview mode');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $apply = $input->getOption('apply');

        $langPacks = new LangPackInfo();

        $engDir   = $langPacks->getLangDir().DIRECTORY_SEPARATOR.'default';
        $engFiles = $this->mapLangFiles($langPacks->getLangDir(), Finder::create()->in($engDir)->files()->name('*.php'));
        $langIds  = ListUtils::filterOutValues($langPacks->getLangIds(), ['default', 'dev_findmissing', 'dev_longstring', 'dev_rtl']);

        $engPhraseCache   = [];
        $langFileCompiler = new LangPhpFileCompiler();

        foreach ($langIds as $langId) {
            $output->writeln(sprintf('********** Processing %s **********', $langId));

            $langDir = $langPacks->getLangDir().DIRECTORY_SEPARATOR.$langId;

            $langFiles = $this->mapLangFiles($langPacks->getLangDir(), Finder::create()->in($langDir)->files()->name('*.php'));

            foreach ($langFiles as $name => $f) {
                if (!isset($engFiles[$name])) {
                    $output->writeln(sprintf('<info>[%s] File: %-21s -- Whole file</info>', $langId, $name));
                    if ($apply) {
                        unlink($f->getRealPath());
                    }
                } else {
                    if (!isset($engPhraseCache[$name])) {
                        $engPhraseCache[$name] = require $engFiles[$name]->getRealPath();
                    }

                    $engPhrases = $engPhraseCache[$name];

                    $langPhrases    = require $f->getRealPath();
                    $newLangPhrases = MapUtils::filter($langPhrases, function ($phraseId, $phrase) use ($engPhrases) {
                        return isset($engPhrases[$phraseId]);
                    });

                    $countBefore = count($langPhrases);
                    $countAfter  = count($newLangPhrases);

                    if ($countBefore !== $countAfter) {
                        $diff = array_diff(array_keys($langPhrases), array_keys($newLangPhrases));
                        $output->writeln(sprintf('<info>[%s] File: %-21s -- Removed %d unknown phrases:</info> %s', $langId, $name, $countBefore - $countAfter, implode(', ', $diff)));

                        if ($apply) {
                            file_put_contents($f->getRealPath(), $langFileCompiler->compilePhpCode($newLangPhrases));
                        }
                    }
                }
            }
        }
    }

    /**
     * @param string         $langDir
     * @param \SplFileInfo[] $files
     *
     * @return \SplFileInfo[] keyed by 'name'
     */
    private function mapLangFiles($langDir, $files)
    {
        $langDir = str_replace('\\', '/', $langDir);
        $map     = [];

        foreach ($files as $f) {
            $path         = $f->getRealPath();
            $langFileName = str_replace('\\', '/', $path);
            $langFileName = str_replace($langDir, '/', $langFileName);
            $langFileName = trim($langFileName, '/');
            $langFileName = substr($langFileName, strpos($langFileName, '/'));

            $map[$langFileName] = $f;
        }

        return $map;
    }
}
