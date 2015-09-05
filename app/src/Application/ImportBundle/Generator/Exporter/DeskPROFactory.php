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

namespace Application\ImportBundle\Generator\Exporter;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\ImportBundle\Reader\BaseConfig;
use Application\ImportBundle\Reader\DeskPRO\DeskPROConfig;
use Application\ImportBundle\Reader\DeskPRO\DeskPROReaderFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * DeskPRO data exporter factory
 *
 * Class DeskPROFactory
 * @package Application\ImportBundle\Generator\Exporter
 */
class DeskPROFactory extends AbstractFactory
{
    /**
     * {@inheritdoc}
     */
    public static function createExporter(ContainerInterface $container, BaseConfig $config)
    {
        if ( ! $config instanceof DeskPROConfig) {
            throw new \RuntimeException('Config expected to be instance of DeskPROConfig');
        }

        /** @var DeskproContainer $container */
        $reader  = DeskPROReaderFactory::createReader($config, $container);

        $tickets = new Parser\DeskPRO\Tickets($reader);
        $tickets->setStartTicketId($config->getStartTicketId());

        $parsers = new Parser\Collection();
        $parsers
            ->attach(new Parser\DeskPRO\People($reader))
            ->attach($tickets)
            ->attach(new Parser\DeskPRO\Articles($reader))
            ->attach(new Parser\DeskPRO\Downloads($reader))
            ->attach(new Parser\DeskPRO\Feedback($reader))
            ->attach(new Parser\DeskPRO\News($reader))
            ->attach(new Parser\DeskPRO\Organizations($reader))
        ;

        return new DeskPRO($parsers, $reader);
    }
}
