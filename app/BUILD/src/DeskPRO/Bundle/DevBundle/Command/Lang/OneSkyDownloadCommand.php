<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\DevBundle\Command\Lang;

use Application\DeskPRO\Languages\LangPackInfo;
use DeskPRO\Bundle\DevBundle\Language\LangPhpFileCompiler;
use DeskPRO\Bundle\DevBundle\Language\OneSky;
use DeskPRO\Component\Util\DebugUtils;
use DeskPRO\Component\Util\ListUtils;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OneSkyDownloadCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dpdev:lang:onesky:download')
            ->setDescription('Downloads phrases from OneSky and into the PHP lang files')
            ->addOption('merge', null, InputOption::VALUE_NONE, 'Merge existing lang with what we donwload (meaning old phrases will continue to exist)')
            ->addOption('export-command-list', null, InputOption::VALUE_NONE, 'Exports a list of commands. E.g. to generate a bash file or a list to use with parallel: parallel --gnu --linebuffer -j 6 {} < exported-command-list')
            ->addArgument('languageId', InputArgument::REQUIRED, 'Which language to upload. This will be a dir name here in the languages/ directory. Use "all" to download all langs.')
            ->addArgument('projectName', InputArgument::OPTIONAL, 'Project to upload: portal, agent, other or the special value all', 'all')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tmpDir = $this->getContainer()->get('deskpro.app_env')->getUserTmpDir();
        $phpBin = $this->getContainer()->get('deskpro.app_env')->getConfig('paths.php_path') ?: 'php';

        $langPacks = new LangPackInfo();
        $reqLangId = $input->getArgument('languageId');
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

        if ($reqLangId !== 'all' && !$langPacks->hasLang($reqLangId)) {
            $output->writeln('<error>Invalid languageId</error>');

            return 1;
        }

        if ($reqLangId === 'default') {
            $output->writeln('<error>You cannot download the default language. The filesystem in DeskPRO is always the source-of-truth for English.</error>');

            return 1;
        }

        if ($reqLangId === 'all') {
            $langIds = $langPacks->getLangIds();
        } else {
            $langIds = [$reqLangId];
        }

        if ($input->getOption('export-command-list')) {
            foreach ($langIds as $lid) {
                if ($lid === 'default' || strpos($lid, 'dev_')) {
                    continue;
                }
                echo "bin/console dpdev:lang:onesky:download {$lid} {$input->getArgument('projectName')}\n";
            }

            return 0;
        }

        $onesky = $this->getContainer()->get('dpdev.onesky');

        //----------------------------------------
        // Read all files from OneSky
        //----------------------------------------

        $output->writeln('******************** Building file list ********************');

        $projectFiles = [];

        foreach ($projectNames as $projectName) {
            $projectId = $onesky->getProjectId($projectName);

            $files = json_decode($onesky->files('list', [
                'project_id' => $projectId,
                'per_page'   => 100,
            ]), true);

            $projectFiles[$projectName] = ListUtils::map($files['data'], function ($r) {
                return $r['file_name'];
            });

            $output->writeln(sprintf('- %s: %d files', $projectName, count($projectFiles[$projectName])));
        }

        //----------------------------------------
        // Read phrases from OneSky for each lang
        //----------------------------------------

        $langFileCompiler = new LangPhpFileCompiler();

        foreach ($langIds as $langId) {
            if ($langId === 'default') {
                continue;
            }

            $lang    = $langPacks->getLangInfo($langId);
            $locale  = $lang['locale'];
            $langDir = str_replace('\\', '/', $langPacks->getLangDir().'/'.$langId);

            $groupedPhrases = [];

            $output->writeln(sprintf('******************** Language: %s (%s) ********************', $langId, $locale));

            foreach ($projectNames as $projectName) {
                $projectId        = $onesky->getProjectId($projectName);
                $projectStartTime = microtime(true);

                foreach ($projectFiles[$projectName] as $fileName) {
                    $output->write(sprintf('[%s] Downloading %s ... ', $projectName, $fileName));
                    $res = $onesky->translations('export', [
                        'project_id'       => $projectId,
                        'locale'           => $locale,
                        'source_file_name' => $fileName,
                        'export_file_name' => 'out.json',
                    ]);
                    if ($res) {
                        $res     = trim($res);
                        $tmpName = tempnam($tmpDir, 'lang_'.$fileName);

                        if (!preg_match('/^<\?php/', $res)) {
                            $res = '<?php return '.$res;
                        }

                        file_put_contents($tmpName, $res);

                        $out = null;
                        if (!DebugUtils::lintPhpFile($tmpName, $out, $phpBin)) {
                            $output->writeln('<error>File appears to be invalid</error>');
                            $output->writeln($out);
                        } else {
                            $phrases = require $tmpName;
                            if (!$phrases) {
                                $output->writeln('Empty file');
                            } else {
                                foreach ($phrases as $phraseId => $phrase) {
                                    $fileId = $langFileCompiler->getFilenameFromPhraseName($phraseId);
                                    if (!isset($groupedPhrases[$fileId])) {
                                        $groupedPhrases[$fileId] = [];
                                    }
                                    $groupedPhrases[$fileId][$phraseId] = $phrase;
                                }
                                $output->writeln('Done');
                            }
                        }

                        @unlink($tmpName);
                    } else {
                        $output->writeln('Empty file');
                    }
                }

                $output->writeln(sprintf("All files in project done in %.3fs\n", microtime(true) - $projectStartTime));
            }

            $output->writeln('Writing lang files to filesystem...');

            foreach ($groupedPhrases as $targetFileName => $phrases) {
                $targetDir = basename($langDir.'/'.$targetFileName);
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755);
                }

                $targetFilePath = $langDir.'/'.$targetFileName;
                if ($input->getOption('merge') && is_file($targetFilePath)) {
                    $filePhrases = require $targetFilePath;
                    if ($filePhrases) {
                        $phrases = array_merge($filePhrases, $phrases);
                    }
                }

                $phpCode = $langFileCompiler->compilePhpCode($phrases);
                file_put_contents($targetFilePath, $phpCode);
            }
        }

        $output->writeln(sprintf('Done all in %.3fs', microtime(true) - $startTime));

        return 0;
    }
}
