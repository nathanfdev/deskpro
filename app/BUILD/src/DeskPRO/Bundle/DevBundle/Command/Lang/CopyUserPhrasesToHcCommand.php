<?php

namespace DeskPRO\Bundle\DevBundle\Command\Lang;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Class CopyIdenticalPhrasesCommand
 *
 * @package DeskPRO\Bundle\DevBundle\Command\Lang
 */
class CopyUserPhrasesToHcCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('dpdev:lang:copy-user-to-hc')
            ->setDescription('Copy any unchanged phrases from user.yml to helpcenter.yml')
            ->addOption('write', 'w', InputOption::VALUE_NONE, 'Write changes to disk')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $DP_ENV \DpRun\DpEnv */
        global $DP_ENV;

        $localeDir = $DP_ENV->getAppDir().'/locales';
        $idMap = [];

        $baseUser = Yaml::parse(file_get_contents($localeDir.'/en-US/user.yml'));
        $baseHc   = Yaml::parse(file_get_contents($localeDir.'/en-US/helpcenter.yml'));

        foreach ($baseHc as $newK => $newT) {
            $name = preg_replace('/^helpcenter\./', '', $newK);
            if (isset($baseUser["user.$newK"])) {
                $idMap["user.$newK"] = $newK;
            } elseif (isset($baseUser["portal.$newK"])) {
                $idMap["portal.$newK"] = $newK;
            } else {
                $findK = array_search($newT, $baseUser);
                if ($findK) {
                    $idMap[$findK] = $newK;
                }
            }
        }

        $dirs = Finder::create()
            ->directories()
            ->in($localeDir)
            ->depth(0)
            ->notName('en-US');

        foreach ($dirs as $d) {
            $langDir  = $localeDir.'/'.$d->getBasename();
            $userFile = $langDir.'/user.yml';
            $hcFile   = $langDir.'/helpcenter.yml';

            if (!file_exists($userFile)) {
                continue;
            }

            $langUser = Yaml::parse(file_get_contents($userFile));

            if (file_exists($hcFile)) {
                $langHc = Yaml::parse(file_get_contents($hcFile));
            } else {
                $langHc = [];
            }

            $did = 0;
            foreach ($idMap as $fromK => $toKey) {
                if (isset($langUser[$fromK])) {
                    $did++;
                    $langHc[$toKey] = $langUser[$fromK];
                }
            }

            if ($did) {
                if ($input->getOption('write')) {
                    echo "Writing $did phrases to $hcFile\n";
                    asort($langHc);
                    file_put_contents($hcFile, Yaml::dump($langHc));
                } else {
                    echo "Could write $did phrases to $hcFile\n";
                }
            }
        }
    }
}
