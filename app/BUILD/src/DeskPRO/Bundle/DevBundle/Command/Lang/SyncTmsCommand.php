<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\DevBundle\Command\Lang;

use DeskPRO\Bundle\DevBundle\Language\DeskproOneSkyTransformer;
use DeskPRO\Bundle\DevBundle\Language\OneSky;
use DeskPRO\Component\Util\ListUtils;
use DeskPRO\Component\Util\MapUtils;
use DeskPRO\Component\Util\Retry;
use DeskPRO\Component\Util\Timer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class SyncTmsCommand extends ContainerAwareCommand
{
    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var array
     */
    private $localeInfo = [];

    /**
     * @var stirng
     */
    private $localeDir;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dpdev:lang:tms:sync')
            ->setDescription('Uploads and downloads phrase data from the translation management system')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Used with download or upload-lang to limit to a specific language')
            ->addOption('wait', 'w', InputOption::VALUE_NONE, 'When uploading or syncing, wait for import job to finish before exiting')
            ->addArgument('action', InputArgument::REQUIRED, 'The action to perform: sync, upload, download, or upload-lang');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;

        $action = strtolower($input->getArgument('action'));

        $this->localeDir  = DP_ROOT.DIRECTORY_SEPARATOR.'/locales';
        $this->localeInfo = MapUtils::map(
            Finder::create()
                ->in($this->localeDir.DIRECTORY_SEPARATOR)
                ->files()
                ->name('localeInfo.yml'),
            function ($idx, \SplFileInfo $f) {
                $data = Yaml::parse(file_get_contents($f->getPathname()));

                return [
                    $data['locale'],
                    $data,
                ];
            }
        );

        switch ($action) {
            case 'sync':
                $input->setOption('wait', true);
                $this->executeUpload('en-US');
                $this->executeDownload('all');
                break;

            case 'upload':
                $this->executeUpload('en-US');
                break;

            case 'upload-lang':
                $this->executeUpload($input->getOption('limit') ?: 'all');
                break;

            case 'download':
                $this->executeDownload($input->getOption('limit') ?: 'all');
                break;

            default:
                $output->writeln('<error>Invalid action.</error>');

                return 1;
        }

        return 0;
    }

    private function executeUpload($locales)
    {
        $fullTimer = Timer::start();
        $locales   = (array) $locales;

        $output = $this->output;

        $appEnv = $this->getContainer()->get('deskpro.app_env');
        $onesky = $this->getOneskyClient();

        $files = [
            'backend.yml',
            'user.yml',
        ];

        if (in_array('all', $locales)) {
            // when upload-lang is specified, we dont want en-US
            $processLocales = ListUtils::filter($this->localeInfo, function (array $l) use ($locales) {
                return $l['locale'] !== 'en-US';
            });
        } else {
            $processLocales = ListUtils::filter($this->localeInfo, function (array $l) use ($locales) {
                return in_array($l['locale'], $locales);
            });
        }

        $importJobs = [];

        foreach ($processLocales as $locale) {
            $output->writeln("<info>[{$locale['locale']}] Processing {$locale['name']}</info>");

            $t = new DeskproOneSkyTransformer("{$this->localeDir}/{$locale['locale']}", $appEnv->getUserTmpDir());

            foreach ($files as $f) {
                $timer = Timer::start();

                $output->writeln("[{$locale['locale']}] Uploading $f");
                $transformedPath = $t->deskproLangFileToOneSky($f);

                $r = Retry::create()->maxTries(3)->throwLast()->returnValue()->run(function ($tryInfo) use ($onesky, $transformedPath, $locale, $output) {
                    if (!$tryInfo['isFirst']) {
                        $output->writeln(sprintf("\tretry (last error: {$tryInfo['lastErrorMessage']})"));
                    }

                    return $onesky->files('upload', [
                        'project_id'             => $onesky->getProjectId('deskpro'),
                        'file'                   => $transformedPath,
                        'file_format'            => 'RUBY_YML',
                        'locale'                 => $locale['locale'],
                        'is_keeping_all_strings' => false,
                    ]);
                });

                $r = json_decode($r, true);

                if (@$r['meta']['status'] != 201) {
                    $output->writeln("<error>[{$locale['locale']}] Unexpected result</error>");
                    print_r($r);

                    return 1;
                }

                if (!empty($r['data']['import']['id'])) {
                    $importJobs[] = $r['data']['import']['id'];
                    $output->writeln("[{$locale['locale']}] Import job: {$r['data']['import']['id']}");
                }

                $timer->end();
                $output->writeln("[{$locale['locale']}] Done upload in {$timer->formatTotalTime()}");
            }
        }

        if ($importJobs && $this->input->getOption('wait')) {
            $output->write(sprintf('Waiting on %d jobs ', count($importJobs)));

            while ($importJobs) {
                $stillWaiting = [];
                foreach ($importJobs as $id) {
                    $v = $onesky->import_tasks('show', [
                        'project_id' => $onesky->getProjectId('deskpro'),
                        'import_id'  => $id,
                    ]);

                    $info = @json_decode($v, true);

                    if (!$info || empty($info['data']['status'])) {
                        $output->writeln("\n<error>Status check on job $id failed!</error>");
                        print_r($v);
                        $stillWaiting[] = $id;
                    } elseif ($info['data']['status'] === 'in-progress') {
                        $stillWaiting[] = $id;
                        echo '.';
                    } elseif ($info['data']['status'] === 'failed') {
                        $output->writeln("\n<error>Job $id failed!</error>");
                    } else {
                        $output->writeln("\nJob $id done");
                    }
                }
                $importJobs = $stillWaiting;

                if ($importJobs) {
                    sleep(3);
                }
            }
        }

        $output->writeln("ALL DONE in {$fullTimer->formatTotalTime()}");
    }

    private function executeDownload($locales)
    {
        $locales = (array) $locales;

        $output = $this->output;

        $appEnv = $this->getContainer()->get('deskpro.app_env');
        $onesky = $this->getOneskyClient();

        $files = [
            'backend.yml',
            'user.yml',
        ];

        $processLocales = ListUtils::filter($this->localeInfo, function (array $l) use ($locales) {
            return $l['locale'] !== 'en-US' && (in_array('all', $locales) || in_array($l['locale'], $locales));
        });

        foreach ($processLocales as $locale) {
            $output->writeln("<info>[{$locale['locale']}] Processing {$locale['name']}</info>");

            $t = new DeskproOneSkyTransformer("{$this->localeDir}/{$locale['locale']}", $appEnv->getUserTmpDir());

            foreach ($files as $f) {
                $timer = Timer::start();

                $output->writeln("[{$locale['locale']}] Downloading $f");
                $r = Retry::create()->maxTries(3)->throwLast()->returnValue()->run(function ($tryInfo) use ($onesky, $f, $locale, $output) {
                    if (!$tryInfo['isFirst']) {
                        sleep(2);
                        $output->writeln(sprintf("\tretry (last error: {$tryInfo['lastErrorMessage']})"));
                    }

                    $data = $onesky->translations('export', [
                        'project_id'       => $onesky->getProjectId('deskpro'),
                        'locale'           => $locale['locale'],
                        'source_file_name' => $f,
                        'export_file_name' => 'out.yml',
                    ]);

                    return $data;
                });

                if ($r) {
                    // OneSky mangles newlines in RTL langs like ar
                    $r = str_replace("\\n\\\n", '\\n', $r);

                    $phraseData = $t->oneSkyDataToDeskproData(Yaml::parse($r));
                } else {
                    $phraseData = [];
                }
                $langFile = $this->localeDir.DIRECTORY_SEPARATOR.$locale['locale'].DIRECTORY_SEPARATOR.$f;
                file_put_contents($langFile, Yaml::dump($phraseData, 3, 2));

                $timer->end();
                $output->writeln("[{$locale['locale']}] Done download in {$timer->formatTotalTime()}");
            }
        }
    }

    /**
     * @return OneSky
     */
    private function getOneskyClient()
    {
        return $this->getContainer()->get('dpdev.onesky');
    }
}
