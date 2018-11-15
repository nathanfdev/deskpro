<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\DevBundle\Command\Gen;

use DeskPRO\Component\Util\ListUtils;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class GenWarmUpFileCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dpdev:gen:warmup-file');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Generating warmup file... This might take a while.');
        $startTime = microtime(true);

        $finder = Finder::create()
            ->in([
                DP_APP_DIR.'/locales/en-US',
                DP_APP_DIR.'/src/Application/DeskPRO/Entity',
                DP_APP_DIR.'/src/Application/DeskPRO/EntityRepository',
                DP_APP_DIR.'/src/DeskPRO/Bundle/AppBundle/Entity',
                DP_APP_DIR.'/sys',
            ])
            ->name('*.php');

        $warmupMapFile = DP_APP_DIR.'/sys/Resources/serverinfo/warmupit.php';

        if (file_exists($warmupMapFile)) {
            $map = require $warmupMapFile;
        } else {
            $map = [];
        }

        /** @var \SplFileInfo $file */
        foreach ($finder as $file) {
            $p     = $file->getRealPath();
            $p     = str_replace('\\', '/', $p);
            $p     = str_replace(str_replace('\\', '/', DP_APP_DIR), '', $p);
            $p     = trim($p, '/');
            $map[] = $p;
        }

        sort($map, \SORT_STRING);
        $map = array_unique($map);
        $map = ListUtils::filter($map, function ($f) {
            return file_exists(DP_APP_DIR.'/'.$f);
        });

        file_put_contents($warmupMapFile, '<?php return '.var_export($map, true).';');

        $output->writeln(sprintf('Wrote map to: <info>%s</info>', $warmupMapFile));
        $output->writeln(sprintf('Done in %.3fs', microtime(true) - $startTime));
    }
}
