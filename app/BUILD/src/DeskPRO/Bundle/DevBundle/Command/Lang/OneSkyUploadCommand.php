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

use Application\DeskPRO\Languages\LangPackInfo;
use DeskPRO\Bundle\DevBundle\Language\LangPhpFileCompiler;
use DeskPRO\Bundle\DevBundle\Language\OneSky;
use DeskPRO\Bundle\DevBundle\Language\PhraseProject;
use DeskPRO\Component\Filesystem\TmpDir;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OneSkyUploadCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dpdev:lang:onesky:upload')
            ->setDescription('Upload phrases to OneSky')
            ->addOption('clean', 'c', InputOption::VALUE_NONE, 'Clean up strings. Strings that are not specified in PHP files here will be deleted in OneSky.')
            ->addArgument('languageId', InputArgument::REQUIRED, 'Which language to upload. This will be a dir name here in the languages/ directory.')
            ->addArgument('projectName', InputArgument::OPTIONAL, 'Project to upload: portal, agent, other or the special value all', 'all')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $langPacks = new LangPackInfo();
        $langId    = $input->getArgument('languageId');
        $startTime = microtime(true);

        switch ($input->getArgument('projectName')) {
            case 'portal':
            case 'agent':
            case 'other':
                $projectNames = [$input->getArgument('projectName')];
                break;
            case 'all':
                $projectNames = ['portal', 'agent', 'other'];
                break;
            default:
                $output->writeln('<error>Invalid projectId</error>');

                return 1;
        }

        if (!$langPacks->hasLang($langId)) {
            $output->writeln('<error>Invalid languageId</error>');

            return 1;
        }

        $lang    = $langPacks->getLangInfo($langId);
        $locale  = $lang['locale'];
        $langDir = str_replace('\\', '/', $langPacks->getLangDir().'/'.$langId);
        $cleanup = $input->getOption('clean');

        $onesky = $this->getContainer()->get('dpdev.onesky');

        $output->writeln(sprintf('<info>Uploading %s (%s)</info>', $lang['title'], $locale));
        $output->writeln(sprintf("Path: %s\n", $langDir, $locale));

        $compiler = new LangPhpFileCompiler();

        foreach ($projectNames as $projectName) {
            $projectId = $onesky->getProjectId($projectName);
            $output->writeln(sprintf('<info>Uploading files in project: %s (%s)</info>', $projectName, $projectId));
            $projectStartTime = microtime(true);

            $project = PhraseProject::createProject(
                $langDir,
                $projectName
            );

            $tmpDir = TmpDir::create($this->getContainer()->get('deskpro.app_env')->getUserTmpDir());

            foreach ($project->getFiles() as $f) {
                $friendlyPath = str_replace($langDir.'/', '', str_replace('\\', '/', $f->getRealPath()));
                $output->write(sprintf('Processing %-40s ... ', $friendlyPath));

                $fileId         = $f->getBasename('.php');
                $groupedPhrases = $project->groupPhrasesFromFile($f);

                foreach ($groupedPhrases as $group => $phrases) {
                    $output->write(sprintf('           %-40s ... ', $friendlyPath));
                    $filePath = $tmpDir.DIRECTORY_SEPARATOR."{$fileId}_{$group}.php";
                    file_put_contents($filePath, $compiler->compilePhpCode($phrases));

                    $upStartTime = microtime(true);
                    $onesky->files('upload', [
                        'project_id'             => $projectId,
                        'file'                   => $filePath,
                        'file_format'            => 'PHP',
                        'locale'                 => $locale,
                        'is_keeping_all_strings' => !$cleanup,
                    ]);
                    $output->writeln(sprintf('Done in %.3fs', microtime(true) - $upStartTime));
                }
            }

            $output->writeln(sprintf("All files in project done in %.3fs\n", microtime(true) - $projectStartTime));
        }

        $output->writeln(sprintf('Done all in %.3fs', microtime(true) - $startTime));

        return 0;
    }
}
