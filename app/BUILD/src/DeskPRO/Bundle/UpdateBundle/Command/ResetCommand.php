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
