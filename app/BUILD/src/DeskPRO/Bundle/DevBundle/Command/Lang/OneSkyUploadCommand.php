<?php

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
        $compiler->enableOldStyleArray();

        foreach ($projectNames as $projectName) {
            $projectId = $onesky->getProjectId($projectName);
            $output->writeln(sprintf('<info>Uploading files in project: %s (%s)</info>', $projectName, $projectId));
            $projectStartTime = microtime(true);

            $project = PhraseProject::createProject(
                $langDir,
                $projectName
            );

            $tmpDir = TmpDir::makeTmpDir($this->getContainer()->get('deskpro.app_env')->getUserTmpDir());

            foreach ($project->getFiles() as $f) {
                $friendlyPath = str_replace($langDir.'/', '', str_replace('\\', '/', $f->getRealPath()));
                $output->writeln(sprintf('Processing %-40s ... ', $friendlyPath));

                $fileId         = $f->getBasename('.php');
                $groupedPhrases = $project->groupPhrasesFromFile($f);

                foreach ($groupedPhrases as $group => $phrases) {
                    if (empty($phrases)) {
                        continue;
                    }
                    $output->write(sprintf('           %-40s ... ', "{$fileId}_{$group}.php"));
                    $filePath = $tmpDir.DIRECTORY_SEPARATOR."{$fileId}_{$group}.php";
                    file_put_contents($filePath, $compiler->compilePhpCode($phrases));

                    $upStartTime = microtime(true);

                    for ($i = 0; $i < 3; ++$i) {
                        try {
                            $onesky->files('upload', [
                                'project_id'             => $projectId,
                                'file'                   => $filePath,
                                'file_format'            => 'PHP',
                                'locale'                 => $locale,
                                'is_keeping_all_strings' => !$cleanup,
                            ]);
                        } catch (\Exception $e) {
                            if ($i === 2) {
                                throw $e;
                            }
                        }
                    }
                    $output->writeln(sprintf('Done in %.3fs', microtime(true) - $upStartTime));
                }
            }

            $output->writeln(sprintf("All files in project done in %.3fs\n", microtime(true) - $projectStartTime));
        }

        $output->writeln(sprintf('Done all in %.3fs', microtime(true) - $startTime));

        return 0;
    }
}
