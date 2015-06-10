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

use Application\ImportBundle\Reader\BaseConfig;
use Application\ImportBundle\Reader\ZenDesk\ZenDeskReaderFactory;
use Application\ImportBundle\Reader\ZenDesk\ZenDeskReaderInterface;
use Application\ImportBundle\Entity;

/**
 * ZenDesk data exporter factory
 *
 * Class ZenDeskFactory
 * @package Application\ImportBundle\Generator\Exporter
 */
class ZenDeskFactory extends AbstractFactory
{
    /**
     * {@inheritdoc}
     */
    static public function createExporter(BaseConfig $config)
    {
        /** @var ZenDeskReaderInterface $reader */
        $reader = ZenDeskReaderFactory::createMockReader($config);
        $storage = new Parser\ZenDesk\PeopleStorage();

        // People parser
        $people = new Parser\ZenDesk\People($reader);
        $people->setPeopleStorage($storage);

        // Tickets parser
        $ticket_people = new Parser\ZenDesk\TicketPeopleStorage($reader);
        $ticket_people->setPeopleStorage($storage);

        $tickets = new Parser\ZenDesk\Tickets($reader, $ticket_people);

        // Parsers collection
        $parsers = new Parser\Collection();
        $parsers
            ->attach(new Parser\ZenDesk\Downloads($reader))
            ->attach(new Parser\ZenDesk\Feedback($reader))
            ->attach(new Parser\ZenDesk\Articles($reader))
            ->attach(new Parser\ZenDesk\News($reader))
            ->attach($people)
            ->attach($tickets);


        return new ZenDesk($parsers, $reader);
    }
}
