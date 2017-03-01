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

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use DeskPRO\Bundle\AppStoreBundle;

class InstallAppCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dp:apps:install')
            ->setDescription('Installs an application')
            ->addArgument('bundle', InputArgument::REQUIRED, 'A path to deskpro app bundle zip file')
        ;
    }

    protected function verifyInputParameters(InputInterface $input, OutputInterface $output)
    {
        $bundleLocation = $input->getArgument('bundle');
        $fileInfo = new \SplFileInfo($bundleLocation);

        if (! $fileInfo->isFile()) {
            $output->writeln("<error>Bundle file not found: $bundleLocation</error>");
            return false;
        }

        if (! $fileInfo->isReadable()) {
            $output->writeln("<error>Bundle file not readable: $bundleLocation</error>");
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (! $this->verifyInputParameters($input, $output)) {
            return 1;
        }

        $bundleLocation = $input->getArgument('bundle');
        $bundle = new AppZipArchiveBundle(new \ZipArchive(), new \SplFileInfo($bundleLocation));

        $applicationCreator = $this->getContainer()->get(AppStoreBundle\Domain\ApplicationCreator::class);
        $validBundle = $applicationCreator->verifyBundle($bundle);

        if (! $validBundle) { //TODO provide a more elaborate exception body
            $output->writeln("<error>File is not a valid deskpro app bundle file: $bundleLocation</error>");
            return 1;
        }

        /** @var AppStoreBundle\Domain\ApplicationInstanceCreator $instanceCreator */
        $instanceCreator = $this->getContainer()->get(AppStoreBundle\Domain\ApplicationInstanceCreator::class);
        $instance = $instanceCreator->createFirstInstance($bundle);

        $output->writeln(sprintf("Successfully created application id : %s</error>", $instance->id));
    }

}
