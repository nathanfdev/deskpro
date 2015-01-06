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

namespace Application\ImportBundle\Generator\Plugin;

use Application\ImportBundle\Generator\AbstractGenerator;
use Application\ImportBundle\Generator\LoggerAwareInterface;

/**
 * Base data generator class methods
 *
 * Class AbstractPlugin
 * @package Application\ImportBundle\Generator\Plugin
 */
abstract class AbstractPlugin extends AbstractGenerator implements GeneratorPluginInterface, LoggerAwareInterface
{
    /**
     * Directory to generated people json files
     *
     * @return string
     */
    protected function getExportPeopleOutputPath()
    {
        return $this->config->getOutputPath() . self::EXPORT_PEOPLE_PATH;
    }

    /**
     * Directory to generated tickets json files
     *
     * @return string
     */
    protected function getExportTicketsOutputPath()
    {
        return $this->config->getOutputPath() . self::EXPORT_TICKETS_PATH;
    }

    /**
     * Directory to generated ticket messages json files
     *
     * @return string
     */
    protected function getExportTicketMessagesOutputPath()
    {
        return $this->config->getOutputPath() . self::EXPORT_TICKET_MESSAGES_PATH;
    }
}
