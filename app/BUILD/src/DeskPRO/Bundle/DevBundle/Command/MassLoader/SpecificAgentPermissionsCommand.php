<?php

namespace DeskPRO\Bundle\DevBundle\Command\MassLoader;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SpecificAgentPermissionsCommand.
 */
class SpecificAgentPermissionsCommand extends AbstractLoadDataCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dpdev:load-data:specific-agent-permissions');
        $this->setDescription('No agent groups. Instead, assign specific permissions to each agent, giving them full permissions to 5 random departments.');
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
        $this->iterate('Create %s agents with random department permissions', 1500, 'loadAgent', [
            'agent_team'  => 'random',
            'departments' => array_fill(0, 5, 'random'),
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
