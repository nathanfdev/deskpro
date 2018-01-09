<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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
