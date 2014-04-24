<?php

namespace DpFixtures\General;

use Application\DeskPRO\Entity\TicketMessage;
use Faker\Factory;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Application\DeskPRO\Entity\Ticket;

class TicketData extends AbstractFixture
{
    protected $data = array(
        0 => array(
            'subject' => 'How to install DeskPRO?',
            'message' => 'Please let me know how can I install DeskPRO in Ubuntu 12.04 LTS 64-bit.',
            'department' => 1,
            'assigned' => 1,
            'follower' => array()
        ),
        1 => array(
            'subject' => 'What is the system requirements for DeskPRO?',
            'message' => 'I would like to know what is the system requirements for installing DeskPRO.'
        ),
        2 => array(
            'subject' => 'How much does DeskPRO cost?',
            'message' => 'DeskPRO has a simple seat based pricing structure based on the number of agents using the helpdesk. The cloud version of DeskPRO costs $30/month/agent and is billed monthly. The downloadable version of DeskPRO costs $10/month/agent and is billed annually. We also offer an unlimited agent license for the downloadable version of the software which is charged at the cost of 100 agents (so $12,000/year).'
        ),
        3 => array(
            'subject' => 'Ticket assignment workflows in DeskPRO',
            'message' => 'Your helpdesk may need several agents to work together to resolve a ticket. For example, a ticket sent to the sales department might need input from the customer service team.Your helpdesk may need several agents to work together to resolve a ticket. For example, a ticket sent to the sales department might need input from the customer service team.Your helpdesk may need several agents to work together to resolve a ticket. For example, a ticket sent to the sales department might need input from the customer service team.'
        ),
        4 => array(
            'subject' => 'Managing Twitter Accounts',
            'message' => "To setup DeskPRO's Twitter integration, you will need to create 2 Twitter applications. A Twitter application allows DeskPRO to access Twitter data. The first application is designed to allow any number of Twitter accounts to be managed by agents, responding to queries and interacting. This application requires full read, write, and direct message access. The second application is for Twitter users that interact with your helpdesk. This application only requires read access (the minimum allowed)."
        )
    );

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        $connection = $manager->getConnection();

        for ($i = 0; $i < 5; $i++) {

            $data = array(
                'subject'         => (isset($this->data[$i])) ? $this->data[$i]['subject'] : $faker->sentence(),
                'date_created'    => date('Y-m-d H:i:s'),
                'person_id'       => 1,
                'department_id'   => 1,
                'creation_system' => Ticket::CREATED_WEB_API,
                'status'          => 'awaiting_agent'
            );

            $ticket = new Ticket(false);
            $ticket = array_merge($ticket->getScalarData(), $data);

            $connection->insert('tickets', $ticket);
            $ticketId = $connection->lastInsertId();

            $message = array(
                'ticket_id'       => $ticketId,
                'person_id'       => 1,
                'is_agent_note'   => 0,
                'creation_system' => TicketMessage::CREATED_WEB_API,
                'message'         => (isset($this->data[$i])) ? $this->data[$i]['message'] : $faker->paragraph(),
                'date_created'    => date('Y-m-d H:i:s')
            );

            $connection->insert('tickets_messages', $message);

        }

    }
} 