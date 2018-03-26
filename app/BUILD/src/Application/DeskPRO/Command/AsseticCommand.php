<?php

/**
 * DeskPRO.
 *
 * @category Commands
 */

namespace Application\DeskPRO\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * dpdev:compile-js.
 *
 * Compiles and minifies JS source files.
 *
 * NOTE: This command assumes default file structure, where assets are stored in
 * /static
 */
class AsseticCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    protected function configure()
    {
        $this->setDefinition([
            new InputArgument('pack', InputArgument::REQUIRED, 'The packs to compile separated by comma. Example: agent_vendors. Or ALL for everything'),
            new InputOption('regex', 'p', InputOption::VALUE_NONE, 'Pack name is interpretted as a regex'),
            new InputOption('not', null, InputOption::VALUE_NONE, 'Pack name is excluded'),
            new InputOption('reload', 'r', InputOption::VALUE_NONE, 'Files are regenerated even if they arent stale'),
        ])->setName('dp:assetic');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $packs           = $input->getArgument('pack');
        $assetic_manager = $this->getContainer()->getSystemService('assetic_manager');

        $bundles = [];

        if ($packs == 'ALL' || $input->getOption('regex')) {
            if ($input->getOption('regex')) {
                foreach ($assetic_manager->getAllBundleNames() as $k) {
                    $match = preg_match('#'.$packs.'#', $k);
                    if ($input->getOption('not') && !$match) {
                        $bundles[] = $k;
                    } elseif (!$input->getOption('not') && $match) {
                        $bundles[] = $k;
                    }
                }
            } else {
                $bundles = $assetic_manager->getAllBundleNames();
            }
        } else {
            foreach (explode(',', $packs) as $p) {
                $p         = trim($p);
                $bundles[] = $p;
            }
        }

        $reload = $input->getOption('reload');

        foreach ($bundles as $name) {
            echo "[PROCESSING] $name ... ";
            try {
                if ($reload) {
                    echo 'reload ';
                    $assetic_manager->writeBuildFile($name);
                } else {
                    $assetic_manager->writeBuildFileIfStale($name);
                }
            } catch (\Exception $e) {
                echo $e->getTraceAsString();
                echo "\n\n";
                $msg = sprintf('[%s] %s', get_class($e), substr($e->getMessage(), 0, 500));
                echo $msg;
                echo "\n\n";
                echo "The last file being compiled was:\n";
                echo 'Pack:     '.$assetic_manager->GetLastPackName()."\n";
                echo 'Tmp File: '.$assetic_manager->getLastFile()."\n";

                return 1;
            }
            echo " Done\n";
        }
    }
}
