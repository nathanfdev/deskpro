<?php

namespace Application\DeskPRO\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TestCommand.
 */
class TestCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dp:test');
    }

    /**
     * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    public function getContainer()
    {
        return parent::getContainer();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        global $DP_ENV;

        echo 'Base:      '.$DP_ENV->getDpRoot();
        echo "\n";
        echo 'Build:     '.$DP_ENV->getAppName();
        echo "\n";
        echo 'Build Dir: '.$DP_ENV->getAppDir();
        echo "\n";

        try {
            $db = $this->getContainer()->getDb();
            echo 'DB:        ' . $db->fetchColumn("SELECT DATABASE()");
            echo "\n";
        } catch (\Exception $e) {
            echo "DB Error:  {$e->getMessage()}\n";
        }

        return 0;
    }
}
