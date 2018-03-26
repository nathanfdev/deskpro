<?php

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
