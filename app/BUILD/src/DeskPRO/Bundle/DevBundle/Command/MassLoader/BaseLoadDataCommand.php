<?php

namespace DeskPRO\Bundle\DevBundle\Command\MassLoader;

use Application\DeskPRO\Entity\Ticket;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BaseLoadDataCommand.
 */
class BaseLoadDataCommand extends AbstractLoadDataCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dpdev:load-data:base');
        $this->setDescription(<<<'EOF'
Agents: 1500
Teams: 15, randomly assign agents to 1 team each
Departments: 200
Agent Groups: Permission to do everything; Assign each group to 5 departments each (so 40 groups total). Assign agents to 2 groups randomly.
15 global filters. 10 of them with just criteria being status:awaiting_agent, 5 of them status:awaiting_user
EOF
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->clearDb();
        $this->iterate('Create %s agent teams', 15, 'loadAgentTeam');
        $this->iterate('Create %s ticket departments', 200, 'loadTicketDepartment');
        $this->iterate('Create %s agent groups', 8, 'loadAgentGroup', [
            'departments' => array_fill(0, 5, 'random'),
        ]);
        $this->iterate('Create %s agents with random group', 1500, 'loadAgent', [
            'agent_team' => 'random',
            'usergroups' => array_fill(0, 2, 'random'),
            'filters'    => [
                [
                    'terms' => [
                        'status' => Ticket::STATUS_AWAITING_USER,
                    ],
                ],
                [
                    'terms' => [
                        'status' => Ticket::STATUS_AWAITING_AGENT,
                    ],
                ],
                [
                    'terms' => [
                        'status' => Ticket::STATUS_AWAITING_AGENT,
                    ],
                ],
                [
                    'terms' => [
                        'status' => Ticket::STATUS_RESOLVED,
                    ],
                ],
                [
                    'terms' => [
                        'status' => Ticket::STATUS_ARCHIVED,
                    ],
                ],
            ],
        ]);
        $this->iterate('Create %s global filters with status:awaiting_agent', 10, 'loadGlobalTicketFilter', [
            'terms' => [
                'status' => 'awaiting_agent',
            ],
        ]);
        $this->iterate('Create %s global filters with status:awaiting_agent', 5, 'loadGlobalTicketFilter', [
            'terms' => [
                'status' => 'awaiting_user',
            ],
        ]);
        $this->iterate('Create %s ticket batches', 10, 'loadTicketBatch', [
            'messagesBatchCount' => 200,
        ]);

        $output->writeln('');
        $output->writeln('Done.');
    }
}
