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

namespace Application\DeskPRO\Elastica;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

class ProgressClosureBuilder
{
    /**
     * Builds a loggerClosure to be called from inside the Provider to update the command
     * line.
     *
     * @param OutputInterface $output
     * @param string          $action
     * @param string          $index
     * @param string          $type
     *
     * @return callable
     */
    public function build(OutputInterface $output, $action, $index, $type)
    {
        if (!class_exists('Symfony\Component\Console\Helper\ProgressBar') ||
            !is_callable(['Symfony\Component\Console\Helper\ProgressBar', 'getProgress'])) {
            return $this->buildLegacy($output, $action, $index, $type);
        }

        $progress = null;

        return function ($increment, $totalObjects, $message = null) use (&$progress, $output, $action, $index, $type) {
            if (null === $progress) {
                $progress = new ProgressBar(new StreamOutput($output->getStream()), $totalObjects);
                $progress->start();
            }

            if (null !== $message) {
                $progress->clear();
                $output->writeln(sprintf('<info>%s</info> <error>%s</error>', $action, $message));
                $progress->display();
            }

            $progress->setMessage(sprintf('<info>%s</info> <comment>%s/%s</comment>', $action, $index, $type));
            $progress->advance($increment);
        };
    }

    /**
     * Builds a legacy closure that outputs lines for each step. Used in cases
     * where the ProgressBar component doesnt exist or does not have the correct
     * methods to support what we need.
     *
     * @param OutputInterface $output
     * @param string          $action
     * @param string          $index
     * @param string          $type
     *
     * @return callable
     */
    private function buildLegacy(OutputInterface $output, $action, $index, $type)
    {
        $lastStep = null;
        $current  = 0;

        return function ($increment, $totalObjects, $message = null) use ($output, $action, $index, $type, &$lastStep, &$current) {
            if ($current + $increment > $totalObjects) {
                $increment = $totalObjects - $current;
            }

            if (null !== $message) {
                $output->writeln(sprintf('<info>%s</info> <error>%s</error>', $action, $message));
            }

            $currentTime      = microtime(true);
            $timeDifference   = $currentTime - $lastStep;
            $objectsPerSecond = $lastStep ? ($increment / $timeDifference) : $increment;
            $lastStep         = $currentTime;
            $current += $increment;
            $percent = 100 * $current / $totalObjects;

            $output->writeln(sprintf(
                '<info>%s</info> <comment>%s/%s</comment> %0.1f%% (%d/%d), %d objects/s (RAM: current=%uMo peak=%uMo)',
                $action,
                $index,
                $type,
                $percent,
                $current,
                $totalObjects,
                $objectsPerSecond,
                round(memory_get_usage() / (1024 * 1024)),
                round(memory_get_peak_usage() / (1024 * 1024))
            ));
        };
    }
}
