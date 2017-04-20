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

use DeskPRO\Bundle\DevBundle\Template\TemplatesScanner;
use DeskPRO\Bundle\PortalBundle\Themes\Base\BaseTheme;
use DeskPRO\Bundle\PortalBundle\Themes\Sidebar\SidebarTheme;
use DeskPRO\Bundle\PortalBundle\Themes\Standard\StandardTheme;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenTemplateMapCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dpdev:gen:template-map');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Generating map... This might take a while.');
        $startTime = microtime(true);

        TemplatesScanner::dump();

        $output->writeln(sprintf('Wrote map to: <info>%s</info>', DP_APP_DIR.TemplatesScanner::DUMP_PATH));

        /** @var \DeskPRO\Bundle\PortalBundle\Theme\AbstractTheme[] $themes */
        $themes = [
            'base'     => new BaseTheme(),
            'standard' => new StandardTheme(),
            'sidebar'  => new SidebarTheme(),
        ];

        $themes['standard']->setParent($themes['base']);
        $themes['sidebar']->setParent($themes['standard']);

        foreach ($themes as $t) {
            $cacheFile = $t->getTemplateMapCachePath();
            $map       = $t->getTemplateMap(true);

            file_put_contents($cacheFile, '<?php return '.var_export($map, true).';');
            $output->writeln(sprintf('Wrote map to <info>%s</info>', $cacheFile));
        }

        $output->writeln(sprintf('Done in %.3fs', microtime(true) - $startTime));
    }
}
