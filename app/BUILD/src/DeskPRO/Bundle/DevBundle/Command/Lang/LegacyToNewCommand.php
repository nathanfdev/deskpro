<?php

namespace DeskPRO\Bundle\DevBundle\Command\Lang;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class LegacyToNewCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dpdev:lang:legacy-to-new')
            ->setDescription('Temporary command used to convert language/ files to locale/ files.')
            ->addArgument('languageId', InputArgument::OPTIONAL, 'Which language to upload. This will be a dir name here in the languages/ directory. Use "all" to download all langs.', 'all')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var \DpRun\DpEnv */
        global $DP_ENV;

        $langDir     = $DP_ENV->getAppDir().'/languages';
        $localeDir   = $DP_ENV->getAppDir().'/locales';
        $manifest    = require($langDir).'/manifest.php';
        $pluralRules = json_decode(file_get_contents(__DIR__.'/data/plural-rules.json'), true);

        $fs = new Filesystem();
        if (file_exists($localeDir)) {
            $fs->remove($localeDir);
        }

        $doLangId = $input->getArgument('languageId');

        foreach ($manifest as $id => $langInfo) {
            if (!($doLangId === 'all' || $doLangId === $id || $doLangId === $langInfo['locale'])) {
                continue;
            }

            $output->writeln("<info>Processing $id</info>");

            $pluralInfo = @$pluralRules[$langInfo['locale']] ?: null;
            if ($pluralInfo === null) {
                if (strpos($langInfo['locale'], '_')) {
                    list($localeLang) = explode('_', $langInfo['locale']);
                    $pluralInfo       = @$pluralRules[$localeLang] ?: null;
                }

                if ($pluralInfo === null) {
                    throw new \InvalidArgumentException('Unknown plural forms for ', $langInfo['locale']);
                }
            }

            $localeInfo = [
                'id'          => $langInfo['id'],
                'name'        => $langInfo['title'],
                'nameLocal'   => $langInfo['title'],
                'locale'      => str_replace('_', '-', $langInfo['locale']),
                'isRtl'       => $langInfo['is_rtl'],
                'pluralRules' => [
                    'plurals'    => $pluralInfo['plurals'],
                    'formula'    => $pluralInfo['formula'],
                    'categories' => $pluralInfo['cases'],
                ],
            ];

            $oldLangDir = $langDir.'/'.$id;
            $targetDir  = $localeDir.'/'.$localeInfo['locale'];

            $phraseGroups = ['user' => [], 'backend' => []];

            /** @var \SplFileInfo[] $files */
            $files = Finder::create()->in($oldLangDir)->files()->name('*.php');
            foreach ($files as $f) {
                switch ($f->getBasename()) {
                    case 'portal.php':
                        $group = 'user';
                        break;
                    default:
                        $group = 'backend';
                        break;
                }

                foreach (require($f->getRealPath()) as $key => $val) {
                    $r = $this->transformPhrase($key, $val, $pluralInfo);

                    if (is_array($r)) {
                        foreach ($r as $subk => $subv) {
                            $phraseGroups[$group][$subk] = $subv;
                        }
                    } else {
                        $phraseGroups[$group][$key] = $r;
                    }
                }
            }

            ksort($phraseGroups['user'], \SORT_STRING);
            ksort($phraseGroups['backend'], \SORT_STRING);

            if (!empty($phraseGroups['user']['user.lang.lang_title'])) {
                $localeInfo['nameLocal'] = $phraseGroups['user']['user.lang.lang_title'];
            }

            $fs->mkdir($targetDir);
            $fs->dumpFile($targetDir.'/user.yml', Yaml::dump($phraseGroups['user'], 2, 2));
            $output->writeln($targetDir.'/user.yml');

            $fs->dumpFile($targetDir.'/backend.yml', Yaml::dump($phraseGroups['backend'], 2, 2));
            $output->writeln($targetDir.'/backend.yml');

            $fs->dumpFile($targetDir.'/localeInfo.yml', Yaml::dump($localeInfo, 3, 2));
            $output->writeln($targetDir.'/localeInfo.yml');
        }
    }

    public function transformPhrase($phraseId, $content, array $pluralInfo)
    {
        // remove spaces in {{ varnames }}
        $content = preg_replace('/\{\{\s*(.*?)\s*\}\}/', '{{$1}}', $content);

        // not a plural, nothing else to do
        if (strpos($content, '{{count}}') === false) {
            return $content;
        }

        $parts = explode('|', $content);

        $phrases = [];

        for ($i = 0; $i < $pluralInfo['plurals']; ++$i) {
            $cat           = $pluralInfo['cases'][$i];
            $phrases[$cat] = @$parts[$i] ?: @$parts[1] ?: $parts[0];
        }

        return [$phraseId => $phrases];
    }
}
