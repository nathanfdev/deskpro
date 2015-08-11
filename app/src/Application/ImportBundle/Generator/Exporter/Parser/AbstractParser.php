<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\Generator\Exporter\Parser;

use Application\ImportBundle\Generator\AbstractGenerator;
use Application\ImportBundle\Generator\GeneratorConfigAwareInterface;
use Application\ImportBundle\Generator\LoggerAwareInterface;
use Application\ImportBundle\Generator\ProgressBarAwareInterface;

/**
 * Abstract generator exporter parser
 *
 * Class AbstractParser
 * @package Application\ImportBundle\Generator\Exporter\Parser
 */
abstract class AbstractParser extends AbstractGenerator implements ParserInterface
{
    /**
     * @var ParserHelperSet
     */
    protected $helpers;

    /**
     * @param string $name
     * @return ParserHelperInterface
     */
    protected function getHelper($name)
    {
        if ( ! $this->helpers instanceof ParserHelperSet) {
            throw new \RuntimeException('Parser helper set is not defined.');
        }

        $helper = $this->helpers->get($name);

        if ($this->config && $helper instanceof GeneratorConfigAwareInterface) {
            /** @var GeneratorConfigAwareInterface $helper */
            $helper->setConfig($this->config);
        }
        if ($this->logger && $helper instanceof LoggerAwareInterface) {
            /** @var LoggerAwareInterface $helper */
            $helper->setLogger($this->logger);
        }
        if ($this->progress_bar && $helper instanceof ProgressBarAwareInterface) {
            /** @var ProgressBarAwareInterface $helper */
            $helper->setProgressBarHelper($this->progress_bar);
        }

        return $helper;
    }
}
