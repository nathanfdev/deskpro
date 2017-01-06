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

namespace DeskPRO\Bundle\DevBundle\Command\MassLoader;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractLoadDataCommand.
 */
abstract class AbstractLoadDataCommand extends ContainerAwareCommand
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @param string $name
     */
    protected function clearSection($name)
    {
        if ($this->output) {
            $this->output->writeln("Clear $name...");
        }

        $loader = $this->getMassLoader();
        $method = 'clear'.ucfirst($name);
        if (!method_exists($loader, $method)) {
            throw new \RuntimeException("Clear method for $name does not exist.");
        }

        $loader->$method();
    }

    /**
     * Clear db before inserting.
     */
    protected function clearDb()
    {
        if ($this->output) {
            $this->output->writeln('Clear db...');
        }

        $this->getMassLoader()->clearDb();
    }

    /**
     * @param string $title
     * @param string $count
     * @param string $method
     * @param array  $options
     */
    protected function iterate($title, $count, $method, array $options = [])
    {
        $progressBar = null;

        if ($this->output) {
            $progressBar = new ProgressBar($this->output, $count);
            $progressBar->setFormat('debug');

            $this->output->writeln('');
            $this->output->writeln(sprintf($title, $count));
        }

        $dataLoader = $this->getMassLoader();
        for ($i = 0; $i < $count; ++$i) {
            $options['isLastBatch'] = ($i === ($count - 1));
            $dataLoader->$method($options);
            if ($progressBar) {
                $progressBar->advance();
            }
        }
    }

    /**
     * @return \DeskPRO\Bundle\DevBundle\MassLoader\MassLoader
     */
    protected function getMassLoader()
    {
        return $this->getContainer()->get('dpdev.mass_loader');
    }
}
