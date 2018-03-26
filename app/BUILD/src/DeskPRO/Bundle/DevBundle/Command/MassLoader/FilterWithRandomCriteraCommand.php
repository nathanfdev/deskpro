<?php

namespace DeskPRO\Bundle\DevBundle\Command\MassLoader;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class FilterWithRandomCriteraCommand.
 */
class FilterWithRandomCriteraCommand extends AbstractLoadDataCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dpdev:load-data:filter-with-random-criteria');
        $this->setDescription('Create 2 filters per-agent with random criteria.');
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
        $this->iterate('Create %s agents with random group and subject contain filters', 1500, 'loadAgent', [
            'agent_team' => 'random',
            'usergroups' => array_fill(0, 2, 'random'),
            'filters'    => [
                [
                    'terms' => [
                        'subject' => [
                            'op'    => 'contains',
                            'value' => 'some text',
                        ],
                    ],
                ],
                [
                    'terms' => [
                        'subject' => [
                            'op'    => 'contains',
                            'value' => 'another text',
                        ],
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

        $output->writeln('');
        $output->writeln('Done.');
    }
}
