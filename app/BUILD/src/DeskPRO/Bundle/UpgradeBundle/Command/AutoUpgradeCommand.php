<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\UpgradeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AutoUpgradeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dp:auto-upgrade')
            ->setDescription('Checks for updates, downloads, and then installs them if they exists')
            ->addOption('as', null, InputOption::VALUE_REQUIRED, 'Save the build as a different build ID')
            ->addOption('sha256', null, InputOption::VALUE_REQUIRED, 'Compare the checksum of the file to this expected value after downloading. This is provided automatically when specying a build from the manifest.')
            ->addOption('skip-existing', null, InputOption::VALUE_NONE, 'Do nothing if the build exists (returns a success code). See also --update-existing.')
            ->addOption('update-existing', null, InputOption::VALUE_NONE, 'Update the build if it exists. The default behaviour is to return an error status. See also --skip-existing.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }
}
