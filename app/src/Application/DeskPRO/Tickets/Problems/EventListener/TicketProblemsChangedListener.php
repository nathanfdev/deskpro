<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at https://www.deskpro.com/eula/                            |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/


namespace Application\DeskPRO\Tickets\Problems\EventListener;

use Application\DeskPRO\DependencyInjection\SystemServices\AgentDataService;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Problem;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\People\PermissionChecker\TicketChecker;
use Doctrine\Common\PropertyChangedListener;
use Doctrine\ORM\PersistentCollection;
use Orb\Util\DpStrings;
use Orb\Util\Strings;

class TicketProblemsChangedListener implements PropertyChangedListener
{
    const CHANNEL = 'agent.ticket-problems-updated';

    /**
     * @var Connection
     */
    protected $conn;

    /**
     * @var \Application\DeskPRO\DependencyInjection\SystemServices\AgentDataService
     */
    protected $agent_data;

    /**
     * @var Person
     */
    private $person;

    /**
     * @var array()
     */
    private $queue = array();

    public function __construct(Connection $conn, AgentDataService $ad)
    {
        $this->conn = $conn;
        $this->agent_data = $ad;
    }

    /**
     * @param Person $person
     */
    public function setPersonContext(Person $person)
    {
        $this->person = $person;
    }

    /**
     * @param object $sender
     * @param string $propertyName
     * @param mixed $oldValue
     * @param mixed $newValue
     */
    public function propertyChanged($sender, $propertyName, $oldValue, $newValue)
    {
        if ('problems' !== $propertyName) {
            return;
        }
        /** @var $sender Ticket */
        /** @var $newValue PersistentCollection */
        $snapshot = $newValue->getSnapshot();
        $old = reset($snapshot) ?: null;
        $new = $newValue->first() ?: null;

        foreach ($this->agent_data->getOnlineAgents() as $agent) {
            $this->sendMessage($sender, $agent, $old, $new);
        }

        $this->sendQueue();
    }

    /**
     * @param Ticket $ticket
     * @param Problem|null $old
     * @param Problem|null $new
     */
    public function sendMessage(Ticket $ticket, Person $forAgent, Problem $old = null, Problem $new = null)
    {
        /** @var TicketChecker $checker */
        $checker = $forAgent->PermissionsManager->TicketChecker;
        if (!$checker->canView($ticket)) {
            return;
        }

        /** @var Person $agent */
        $dis = array();
        $ass = array();


        if ($checker->canView($ticket))

        if ($old) {
            $dis[] = $old['id'];
        }

        if ($new) {
            $ass[] = $new['id'];
        }


        $this->queue[] = array(
            'for_person_id' => $forAgent->id,
            'channel' => self::CHANNEL,
            'auth' => DpStrings::random(15, Strings::CHARS_KEY),
            'date_created' => date('Y-m-d H:i:s'),
            'data' => serialize(
                array(
                    'ticket_id' => $ticket->id,
                    'ticket_agent_id' => $ticket->agent ? $ticket->agent->id : null,
                    'ticket_agent_team_id' => $ticket->agent_team ? $ticket->agent_team->id : null,
                    'disassociated' => $dis,
                    'associated' => $ass,
                    'via_person' => $this->person ? $this->person->id : null
                )
            ),
        );
    }

    /**
     * Send all messages.
     *
     * @return int How many messages were sent
     */
    public function sendQueue()
    {
        if (!$this->queue) {
            return 0;
        }

        $q = $this->queue;
        $this->queue = array();

        $this->conn->batchInsert('client_messages', $q);

        return count($q);
    }
}