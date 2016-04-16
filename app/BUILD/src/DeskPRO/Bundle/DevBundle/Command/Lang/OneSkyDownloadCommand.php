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
use DeskPRO\Component\Util\DebugUtils;
use DeskPRO\Component\Util\ListUtils;
use DeskPRO\Component\Util\MapUtils;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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

        $onesky = $this->getContainer()->get('dpdev.onesky');

        #----------------------------------------
        # Read all files from OneSky
        #----------------------------------------

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

        #----------------------------------------
        # Read phrases from OneSky for each lang
        #----------------------------------------

        $langFileCompiler = new LangPhpFileCompiler();

        foreach ($langIds as $langId) {
            if ($langId === 'default') {
                continue;
            }

            $lang    = $langPacks->getLangInfo($langId);
            $locale  = $lang['locale'];
            $langDir = str_replace('\\', '/', $langPacks->getLangDir().'/'.$reqLangId);

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
                        $tmpName = tempnam($tmpDir, 'lang_'.$fileName);
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
                                $targetFileName = $langFileCompiler->getFilenameFromPhraseName(MapUtils::firstKey($phrases));
                                $phpCode        = $langFileCompiler->compilePhpCode($phrases);
                                file_put_contents($langDir.'/'.$targetFileName, $phpCode);
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
        }

        $output->writeln(sprintf('Done all in %.3fs', microtime(true) - $startTime));

        return 0;
    }
}
