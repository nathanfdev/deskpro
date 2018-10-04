<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\DevBundle\Command\Gen;

use Application\DeskPRO\Languages\LangPackInfo;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class OptLangCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dpdev:gen:opt-lang');
        $this->setDescription('Converts YAML lang files to PHP files, and generates a manifest.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $langDir = DP_ROOT.DIRECTORY_SEPARATOR.'/locales';
        $fs      = new Filesystem();

        // clear existing opt files
        $fs->remove(
            Finder::create()
                ->in($langDir)
                ->files()
                ->name('*.php')
        );

        $langPackInfo = new LangPackInfo();
        $manifestData = [];

        foreach ($langPackInfo->getLangPacks() as $l) {
            $manifestData[$l['id']] = $l;

            foreach (
                Finder::create()
                    ->in($langDir.DIRECTORY_SEPARATOR.$l['locale'])
                    ->files()
                    ->name('*.yml')
                as $f
            ) {
                /* @var $f \SplFileInfo */
                $output->writeln("Dumping {$f->getPathname()}...");
                $fileData = Yaml::parse(file_get_contents($f->getPathname()));
                file_put_contents(str_replace('.yml', '.php', $f->getPathname()), '<?php return '.var_export($fileData, true).';'."\n");
            }
        }

        $output->writeln('Dumping manifest...');
        file_put_contents($langDir.DIRECTORY_SEPARATOR.'manifest.php', '<?php return '.var_export($manifestData, true).';'."\n");

        $output->writeln('All done');
    }
}
