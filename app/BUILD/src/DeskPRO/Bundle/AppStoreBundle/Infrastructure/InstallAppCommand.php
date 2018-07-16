<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure;

use DeskPRO\Bundle\AppStoreBundle;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\AppBundleAdapters\BundleFileHandlingStrategyZip;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        $fileInfo       = new \SplFileInfo($bundleLocation);

        if (!$fileInfo->isFile()) {
            $output->writeln("<error>Bundle file not found: $bundleLocation</error>");

            return false;
        }

        if (!$fileInfo->isReadable()) {
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
        if (!$this->verifyInputParameters($input, $output)) {
            return 1;
        }

        $bundleLocation = $input->getArgument('bundle');
        /** @var BundleFileHandlingStrategyZip $bundleReader */
        $bundleReader   = $this->getContainer()->get(BundleFileHandlingStrategyZip::class);
        $bundle         = $bundleReader->reader($bundleLocation);

        $bundleValidator = $this->getContainer()->get(AppStoreBundle\Domain\AppBundleValidator::class);
        $validBundle     = $bundleValidator->verifyBundle($bundle);

        if (!$validBundle) { //TODO provide a more elaborate exception body
            $output->writeln("<error>File is not a valid deskpro app bundle file: $bundleLocation</error>");

            return 1;
        }

        $instanceCreator = $this->getContainer()->get('apps2.application_manager');
        $instance        = $instanceCreator->createFirstInstance($bundle);

        $output->writeln(sprintf('Successfully created application id : %s</error>', $instance->getId()));
    }
}
