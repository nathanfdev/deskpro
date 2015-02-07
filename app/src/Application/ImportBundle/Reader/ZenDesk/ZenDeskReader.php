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

namespace Application\ImportBundle\Reader\ZenDesk;

use Zendesk\API\Client;

/**
 * ZenDesk reader
 *
 * see https://developer.zendesk.com/rest_api/docs/core/incremental_export
 *
 * Class ZenDeskReader
 * @package Application\ImportBundle\Reader\ZenDesk
 */
class ZenDeskReader implements ZenDeskReaderInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * Constructor
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     *
     * There is no method count(), but method findAll() returns total collection count value
     */
    public function getPeopleCount()
    {
        $result = $this->client->users()->findAll(array('per_page' => 1));
        return $result->count;
    }

    /**
     * {@inheritdoc}
     */
    public function getPeople()
    {
        $people = array();
        $result = $this->client->users()->findAll(array(
            'page'       => 1,
            'per_page'   => 100,
            'sort_by'    => 'id',
            'sort_order' => 'desc',
            'start_time' => time(), // todo see https://support.zendesk.com/hc/en-us/articles/204232743
        ));

//        var_dump($result->next_page);
//        var_dump($result->previous_page);
//        var_dump($result->count);

        if (is_array($result->users)) {
            foreach ($result->users as $person) {
                $people[] = (array)$person;
            }
        }

        return $people;
    }

    /**
     * {@inheritdoc}
     *
     * There is no method count(), but method findAll() returns total collection count value
     */
    public function getTicketsCount()
    {
        $result = $this->client->tickets()->findAll(array('per_page' => 1));
        return $result->count;
    }

    /**
     * {@inheritdoc}
     */
    public function getTickets()
    {
        $tickets = array();
        $result  = $this->client->tickets()->findAll();

        if (is_array($result->tickets)) {
            foreach ($result->tickets as $ticket) {
                $tickets[] = (array)$ticket;
            }
        }

        return $tickets;
    }
}
