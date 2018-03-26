<?php

/**
 * DeskPRO.
 */

namespace DpBehat;

use Application\DeskPRO\Command\IndexElasticsearchCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Class ElasticContext.
 */
class ElasticContext extends BaseContext
{
    private static $populated = false;

    /**
     * @Given I populate elastic index
     */
    public function iPopulateElasticIndex()
    {
        if (self::$populated) {
            echo 'index is already populated';

            return;
        }

        $install_start = time();

        $command = new IndexElasticsearchCommand();
        $output  = new BufferedOutput();

        $application = new Application($this->kernel());
        $application->add($command);

        $command->run(new ArrayInput(['command' => 'dp:elastica:index']), $output);
        self::$populated = true;

        echo 'index was successfully repopulated (took '.(time() - $install_start).' seconds)';
    }
}
