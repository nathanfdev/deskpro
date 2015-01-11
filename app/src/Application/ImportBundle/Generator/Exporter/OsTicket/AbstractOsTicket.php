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

namespace Application\ImportBundle\Generator\Exporter\OsTicket;

use Application\ImportBundle\Generator\Exporter\AbstractExporter;
use Application\ImportBundle\Generator\Exporter\EntityExporterInterface;
use Application\ImportBundle\OsTicket\OsTicketReader;
use Application\ImportBundle\OsTicket\OsTicketReaderInterface;

/**
 * Class AbstractOsTicket
 * @package Application\ImportBundle\Generator\Exporter\OsTicket
 */
abstract class AbstractOsTicket extends AbstractExporter implements EntityExporterInterface
{
    /**
     * @var OsTicketReader
     */
    protected $os_ticket_reader;

    /**
     * Constructor
     *
     * @param OsTicketReaderInterface $os_ticket_reader
     */
    public function __construct(OsTicketReaderInterface $os_ticket_reader)
    {
        $this->os_ticket_reader = $os_ticket_reader;
    }
}
