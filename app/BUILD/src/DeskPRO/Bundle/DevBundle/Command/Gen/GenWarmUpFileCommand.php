<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
                DP_APP_DIR.'/languages/default',
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
