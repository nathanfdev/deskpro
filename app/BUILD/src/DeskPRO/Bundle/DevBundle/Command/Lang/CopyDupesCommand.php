<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\DevBundle\Command\Lang;

use DeskPRO\Bundle\DevBundle\Language\LangPhpFileCompiler;
use DeskPRO\Bundle\DevBundle\Language\PhraseProject;
use DeskPRO\Component\Util\ListUtils;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CopyDupesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dpdev:lang:copy-dupes')
            ->setDescription(<<<STR
This tool expects JSON to be provided as input via stdin which describes groups of phrases
considered to be equivalent. (For example, there might be two phrases "Contact Us" that exist
as two separate phrase IDs.)

The tool will determine missing phraseIds from the current local set of language files,
and then attempt to fill them in by copying an equivalent phrase.

Optionally you can specify sourceDir as an extra directory to scan for equivalent phrases
(for example, from an older version where phrases might have been deleted).
STR
            )
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
                'langs',
                'l',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Specify specific languages to process (instead of all of them by default)',
                []
            )
            ->addOption('apply', 'x', InputOption::VALUE_NONE, 'Write changes to disk. Without this, it is just a preview mode')
            ->addOption('alt', null, InputOption::VALUE_REQUIRED, 'An old/alt lang dir for the langs')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $env     = $this->getContainer()->get('deskpro.app_env');
        $engDir  = $env->getAppDir().'/languages/default';
        $doApply = $input->getOption('apply');

        $altSourceDir = $input->getOption('alt');
        if ($altSourceDir && !($altSourceDir = realpath($altSourceDir))) {
            $output->writeln('<error>Invalid altSourceDir</error>');

            return 1;
        }

        #------------------------------
        # Decide which zones to work on
        #------------------------------

        $zones       = $input->getOption('zone');
        $ignoreZones = $input->getOption('ignore-zone');

        if (in_array('all', $zones)) {
            $zones = [];
            $iter  = new \FilesystemIterator($engDir);
            /** @var \SplFileInfo $f */
            foreach ($iter as $f) {
                if ($f->isDir()) {
                    $zones[] = $f->getBasename();
                }
            }
        }

        if ($ignoreZones) {
            $zones = ListUtils::filter($zones, function ($v) use ($ignoreZones) {
                return !in_array($v, $ignoreZones);
            });
        }

        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln('Operating on phrases in zones: '.implode(', ', $zones));
        }

        #------------------------------
        # Load phrase IDs
        #------------------------------

        $engProject   = PhraseProject::createProject($engDir, $zones);
        $engPhraseIds = [];

        foreach ($engProject->getFilesMap() as $fileId => $projectFile) {
            $engPhraseIds[$fileId] = $this->readPhraseIds($projectFile);
        }

        #------------------------------
        # Read and process stdin
        #------------------------------

        $stdIn = trim(stream_get_contents(STDIN));

        $equivPhrases    = json_decode($stdIn, true);
        $equivPhrasesMap = [];

        if (empty($equivPhrases)) {
            $output->writeln('<error>Empty equiv phrases collection. There is nothing this tool can do.</error>');

            return 1;
        }

        foreach ($equivPhrases as $phraseGroup) {
            foreach ($phraseGroup as $id) {
                $equivPhrasesMap[$id] = $phraseGroup;
            }
        }

        #------------------------------
        # Process each lang dir
        #------------------------------

        $langs = $input->getOption('langs') ?: null;

        $langCompiler = new LangPhpFileCompiler();

        $dir = dir($env->getAppDir().'/languages');
        while ($langId = $dir->read()) {

            // skip langs we dont want to process
            if ($langs && !in_array($langId, $langs)) {
                continue;
            }

            if (strpos($langId, 'dev') === 0 || $langId === 'english_gb') {
                continue;
            }

            $output->writeln(sprintf('[%s] Processing', $langId));

            $path = $env->getAppDir().'/languages/'.$langId;

            if ($langId === '.' || $langId === '..' || $langId === 'default' || !is_dir($path)) {
                continue;
            }

            $project     = PhraseProject::createProject($path, $zones);
            $langPhrases = $this->readLangPhrases($project);

            // Mix in the alt source dir so we can use it as another source
            if ($altSourceDir) {
                $altProject  = PhraseProject::createProject($altSourceDir.'/'.$langId, $zones);
                $langPhrases = array_merge($this->readLangPhrases($altProject), $langPhrases);
            }

            foreach ($engProject->getFilesMap() as $fileId => $phraseFile) {
                $phraseIds = $engPhraseIds[$fileId];
                $langFile  = $project->getFileByFileId($fileId);

                if (!$langFile) {
                    $missingPhraseIds = $phraseIds;
                    $langFilePath     = $path.'/'.$fileId;
                } else {
                    $langPhrasesIds   = $this->readPhraseIds($langFile);
                    $missingPhraseIds = array_diff($phraseIds, $langPhrasesIds);
                    $langFilePath     = $langFile->getPathname();
                }

                if (!empty($missingPhraseIds)) {
                    $output->writeln(sprintf('[%s] %s: Found %d missing phrases', $langId, $fileId, count($missingPhraseIds)));

                    if (!$langFile) {
                        $filePhrases = [];
                    } else {
                        $filePhrases = require $langFile->getPathname();
                    }

                    $countCopy = 0;
                    foreach ($missingPhraseIds as $phraseId) {
                        if (!isset($equivPhrasesMap[$phraseId])) {
                            // no equiv phrases to guess from
                            continue;
                        }

                        foreach ($equivPhrasesMap[$phraseId] as $equivPhraseId) {
                            if (!empty($langPhrases[$equivPhraseId])) {
                                $filePhrases[$phraseId] = $langPhrases[$equivPhraseId];
                                $output->writeln(sprintf('    <info>Copy %s from %s</info>', $phraseId, $equivPhraseId));
                                ++$countCopy;
                                break;
                            }
                        }
                    }

                    if ($countCopy) {
                        $output->writeln(sprintf('[%s] %s: Can copy %d phrases', $langId, $fileId, $countCopy));
                    }

                    if ($countCopy && !empty($filePhrases)) {
                        if ($doApply) {
                            $output->writeln(sprintf('[%s] %s: Wrote %s', $langId, $fileId, $langFilePath));
                            file_put_contents($langFilePath, $langCompiler->compilePhpCode($filePhrases));
                        } else {
                            $output->writeln(sprintf('[%s] %s: Use --apply to write file', $langId, $fileId));
                        }
                    }
                }
            }
        }

        return 0;
    }

    /**
     * @param PhraseProject $project
     *
     * @return array
     */
    private function readLangPhrases(PhraseProject $project)
    {
        $phrases = [];

        foreach ($project->getFiles() as $f) {
            $phrases = array_merge($phrases, require($f->getPathname()));
        }

        return $phrases;
    }

    /**
     * @param \SplFileInfo $f
     *
     * @return array
     */
    private function readPhraseIds(\SplFileInfo $f)
    {
        $phrases = require $f->getPathname();

        return array_keys($phrases);
    }
}
