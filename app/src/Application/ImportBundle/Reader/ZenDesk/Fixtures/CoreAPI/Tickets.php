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

namespace Application\ImportBundle\Reader\ZenDesk\Fixtures\CoreAPI;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Reader\ZenDesk\Fixtures\AbstractFixture;
use Zendesk\API\Client;
use Zendesk\API\ResponseException;
use DateTime;

/**
 * ZenDesk tickets fixtures
 *
 * Class Tickets
 * @package Application\ImportBundle\Reader\ZenDesk\Fixtures\CoreAPI
 */
final class Tickets extends AbstractFixture
{
    /**
     * @var PeopleLoader
     */
    private $people_loader;

    /**
     * Constructor
     *
     * @param Client       $client
     * @param PeopleLoader $people_loader
     */
    public function __construct(Client $client, PeopleLoader $people_loader)
    {
        parent::__construct($client);
        $this->people_loader = $people_loader;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_TICKET;
    }

    /**
     * {@inheritdoc}
     */
    protected function createItem($num, DateTime $initial_time, DateTime $end_time)
    {
        $types      = array('problem', 'incident', 'question', 'task');
        $priorities = array('urgent', 'high', 'normal', 'low');
        $statuses   = array('open', 'pending', 'hold', 'solved', 'closed', 'deleted');

        $type   = $types[rand(0, count($types) - 1)];
        $params = array(
            'subject' => 'Fake ticket ' . $num,
            'comment' => array(
                'type'       => 'Comment',
                'body'       => 'Thanks for your help!',
                'public'     => true,
            ),
            'type'         => $type,
            'priority'     => $priorities[rand(0, count($priorities) - 1)],
            'status'       => $statuses[rand(0, count($statuses) - 1)],
            'requester_id' => $this->people_loader->getRandomPersonId(),
            'submitter_id' => $this->people_loader->getRandomPersonId(),
        );

        if ($type === 'task') {
            $params['due_at'] = $this->getRandomDateTime($initial_time, $end_time)->format('Y-m-d');
        }

        $response = $this->client->tickets()->create($params);
        $this->logger->info('Ticket created successfully');
        $this->logger->debug(json_encode($response->ticket));

        for ($i = 1; $i <= 100; $i++) {
            try {
                $comment = $this->client->tickets()->update(array(
                    'id'      => $response->ticket->id,
                    'comment' => array(
                        'type'   => 'Comment',
                        'body'   => 'Reply #' . $i,
                        'public' => true,
                    ),
                ));

                $this->logger->info('Ticket comment created successfully');
                $this->logger->debug(json_encode($comment));

            } catch (ResponseException $e) {
                $this->handleResponseException('ticket_comment');
            }
        }
    }
}
