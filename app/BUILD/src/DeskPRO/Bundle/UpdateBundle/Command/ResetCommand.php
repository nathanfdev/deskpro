<?php

namespace DeskPRO\Bundle\UpdateBundle\Command;

use DeskPRO\Bundle\UpdateBundle\BuildActivate\HelpdeskState\HelpdeskStateModifier;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class ResetCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dp:update:reset')
            ->setDescription(
                'This unsets various "upgrade in process" markers that may be set. Use this if an upgrade failed and you '
                .'have manually fixed a problem, you can use this to remove any "maintenance" messages. Note that '
                .'this tool will NOT cancel an already-in-progress upgrade (i.e. another process running on your server).'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        global $DP_ENV;

        $files = [
            $DP_ENV->getUserCacheDir().DIRECTORY_SEPARATOR.HelpdeskStateModifier::OFFLINE_TRIGGER_NAME.'.trigger',
            $DP_ENV->getUserCacheDir().DIRECTORY_SEPARATOR.HelpdeskStateModifier::ACTIVE_BUILD_FILENAME,
        ];

        $fs = new Filesystem();

        foreach ($files as $filePath) {
            try {
                $output->writeln("<info>Removing: $filePath</info>");
                $fs->remove($filePath);
                $output->writeln('.. done');
            } catch (\Exception $e) {
                $output->writeln("<error>Failed: [{$e->getCode()}] {$e->getMessage()})</error>");
            }
        }
    }
}
