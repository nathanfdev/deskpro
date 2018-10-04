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

        $langDir   = $DP_ENV->getAppDir().'/languages';
        $localeDir = $DP_ENV->getAppDir().'/locales';
        $manifest  = require($langDir).'/manifest.php';

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

            $pluralInfo = $this->getPluralFormInfo($langInfo['locale']);
            if ($pluralInfo === null) {
                if (strpos($langInfo['locale'], '_')) {
                    list($localeLang) = explode('_', $langInfo['locale']);
                    $pluralInfo       = $this->getPluralFormInfo($localeLang);
                }

                if ($pluralInfo === null) {
                    throw new \InvalidArgumentException('Unknown plural forms for ', $langInfo['locale']);
                }
            }

            $oldLangDir = $langDir.'/'.$id;
            $targetDir  = $localeDir.'/'.$langInfo['locale'];

            $phraseGroups = ['user' => [], 'backend' => []];

            $localeInfo = [
                'id'          => $langInfo['id'],
                'name'        => $langInfo['title'],
                'nameLocal'   => $langInfo['title'],
                'locale'      => $langInfo['locale'],
                'isRtl'       => $langInfo['is_rtl'],
                'pluralForms' => [
                    'count'     => $pluralInfo[0],
                    'selectors' => [
                        'php' => $pluralInfo[1],
                        'js'  => str_replace('$n', 'n', $pluralInfo[1]),
                    ],
                ],
            ];

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

        if ($pluralInfo[0] == 2) {
            $phrases[$phraseId]            = $parts[0];
            $phrases["{$phraseId}_plural"] = @$parts[1] ?: $parts[0];
        } else {
            for ($i = 0; $i < $pluralInfo[0]; ++$i) {
                $phrases["{$phraseId}_$i"] = @$parts[$i] ?: @$parts[1] ?: $parts[0];
            }
        }

        return $phrases;
    }

    // returns array of [numPluralForms, formSelectorExpression]
    private function getPluralFormInfo($locale)
    {
        switch ($locale) {
            case 'az':
            case 'bo':
            case 'dz':
            case 'id':
            case 'ja':
            case 'jv':
            case 'ka':
            case 'km':
            case 'kn':
            case 'ko':
            case 'ms':
            case 'th':
            case 'tr':
            case 'vi':
            case 'zh':
                return [0, null];

            case 'af':
            case 'bn':
            case 'bg':
            case 'ca':
            case 'da':
            case 'de':
            case 'el':
            case 'en':
            case 'eo':
            case 'es':
            case 'et':
            case 'eu':
            case 'fa':
            case 'fi':
            case 'fo':
            case 'fur':
            case 'fy':
            case 'gl':
            case 'gu':
            case 'ha':
            case 'he':
            case 'hu':
            case 'is':
            case 'it':
            case 'ku':
            case 'lb':
            case 'ml':
            case 'mn':
            case 'mr':
            case 'nah':
            case 'nb':
            case 'ne':
            case 'nl':
            case 'nn':
            case 'no':
            case 'oc':
            case 'om':
            case 'or':
            case 'pa':
            case 'pap':
            case 'ps':
            case 'pt':
            case 'so':
            case 'sq':
            case 'sv':
            case 'sw':
            case 'ta':
            case 'te':
            case 'tk':
            case 'ur':
            case 'zu':
                return [2, '(1 == $n) ? 0 : 1'];

            case 'am':
            case 'bh':
            case 'fil':
            case 'fr':
            case 'gun':
            case 'hi':
            case 'hy':
            case 'ln':
            case 'mg':
            case 'nso':
            case 'xbr':
            case 'ti':
            case 'wa':
                return [2, '((0 == $n) || (1 == $n)) ? 0 : 1'];

            case 'be':
            case 'bs':
            case 'hr':
            case 'ru':
            case 'sh':
            case 'sr':
            case 'uk':
                return [3, '((1 == $n % 10) && (11 != $n % 100)) ? 0 : ((($n % 10 >= 2) && ($n % 10 <= 4) && (($n % 100 < 10) || ($n % 100 >= 20))) ? 1 : 2)'];

            case 'cs':
            case 'sk':
                return [3, '(1 == $n) ? 0 : ((($n >= 2) && ($n <= 4)) ? 1 : 2)'];

            case 'ga':
                return [3, '(1 == $n) ? 0 : ((2 == $n) ? 1 : 2)'];

            case 'lt':
                return [3, '((1 == $n % 10) && (11 != $n % 100)) ? 0 : ((($n % 10 >= 2) && (($n % 100 < 10) || ($n % 100 >= 20))) ? 1 : 2)'];

            case 'sl':
                return [4, '(1 == $n % 100) ? 0 : ((2 == $n % 100) ? 1 : (((3 == $n % 100) || (4 == $n % 100)) ? 2 : 3))'];

            case 'mk':
                return [2, '(1 == $n % 10) ? 0 : 1'];

            case 'mt':
                return [4, '(1 == $n) ? 0 : (((0 == $n) || (($n % 100 > 1) && ($n % 100 < 11))) ? 1 : ((($n % 100 > 10) && ($n % 100 < 20)) ? 2 : 3))'];

            case 'lv':
                return [3, '(0 == $n) ? 0 : (((1 == $n % 10) && (11 != $n % 100)) ? 1 : 2)'];

            case 'pl':
                return [3, '(1 == $n) ? 0 : ((($n % 10 >= 2) && ($n % 10 <= 4) && (($n % 100 < 12) || ($n % 100 > 14))) ? 1 : 2)'];

            case 'cy':
                return [4, '(1 == $n) ? 0 : ((2 == $n) ? 1 : (((8 == $n) || (11 == $n)) ? 2 : 3))'];

            case 'ro':
                return [3, '(1 == $n) ? 0 : (((0 == $n) || (($n % 100 > 0) && ($n % 100 < 20))) ? 1 : 2)'];

            case 'ar':
                return [6, '(0 == $n) ? 0 : ((1 == $n) ? 1 : ((2 == $n) ? 2 : ((($n % 100 >= 3) && ($n % 100 <= 10)) ? 3 : ((($n % 100 >= 11) && ($n % 100 <= 99)) ? 4 : 5))))'];

            default:
                return null;
        }
    }
}
