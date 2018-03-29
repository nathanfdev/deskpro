<?php

namespace DeskPRO\Bundle\AppBundle\Command\Configure;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ToggleFeatureCommand.
 */
class ToggleFeatureCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('dp:config:toggle-feature')
            ->setDescription('Toggle beta feature')
            ->addArgument('id', InputArgument::REQUIRED, 'Feature id')
            ->addOption('action', 'a', InputOption::VALUE_REQUIRED)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $collection    = $this->getContainer()->get('deskpro.features_collection');
        $toggleManager = $this->getContainer()->get('deskpro.toggle_feature_manager');
        $featureId     = $input->getArgument('id');
        $action        = $input->getOption('action');

        if ($action === 'enable') {
            $toggleManager->enableFeature($featureId);
        } elseif ($action === 'disable') {
            $toggleManager->disableFeature($featureId);
        } else {
            // if no action, toggle feature based on current state
            $feature = $collection->getFeature($input->getArgument('id'));
            if (!$feature) {
                $output->writeln("<error>Feature '$featureId' not found</error>");

                return 1;
            }

            try {
                if ($feature->isEnabled()) {
                    $toggleManager->disableFeature($featureId);
                } else {
                    $toggleManager->enableFeature($featureId);
                }
            } catch (\Exception $e) {
                $output->writeln("<error>{$e->getMessage()}</error>");

                return 1;
            }
        }

        return 0;
    }
}
