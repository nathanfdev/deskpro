<?php

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
