<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\DevBundle\Command\LoadData;

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
        $this->iterate('Create %s agents with random group', 1500, 'loadAgent', [
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
    }
}
