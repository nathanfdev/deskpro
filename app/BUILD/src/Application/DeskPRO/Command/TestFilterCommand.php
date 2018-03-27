<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Command;

use Orb\Util\Strings;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestFilterCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dp:test-filter');
        $this->addOption('ticketlist', null, InputOption::VALUE_NONE, 'Render ticket results as text');
        $this->addOption('group', null, InputOption::VALUE_REQUIRED, 'Group results: group:value');
        $this->addArgument('agent', InputArgument::REQUIRED, 'Email/ID for an agent to run the filter as');
        $this->addArgument('filterId', InputArgument::REQUIRED, 'Filter ID to run');
    }

    /**
     * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    public function getContainer()
    {
        return parent::getContainer();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        if (!ctype_digit($input->getArgument('agent'))) {
            $agent = $container->getAgentData()->getByEmail($input->getArgument('agent'));
        } else {
            $agent = $container->getAgentData()->get($input->getArgument('agent'));
        }

        if (!$agent) {
            $output->writeln('<error>Could not find that agent</error>');

            return 1;
        }

        $agent->loadHelper('Agent');
        $agent->loadHelper('AgentTeam');
        $agent->loadHelper('AgentPermissions');
        $agent->loadHelper('PermissionsManager');
        $agent->loadHelper('HelpMessages');
        $agent->loadHelper('AgentPrefs');

        /** @var \Application\DeskPRO\Entity\TicketFilter $filter */
        $filter = $container->getEm()->find('DeskPRO:TicketFilter', $input->getArgument('filterId'));
        if (!$filter) {
            $output->writeln('<error>Could not find that filter</error>');

            return 1;
        }

        $output->writeln(sprintf('<info>Filter #%d: %s</info>', $filter->id, $filter->title));

        $searcher = $filter->getSearcher();
        $searcher->setOrderBy('ticket.date_created', 'DESC');
        $searcher->setPerson($agent);

        if ($input->getOption('group')) {
            list($set_group_term, $set_group_option) = explode(':', $input->getOption('group'));
            $term                                    = \Application\DeskPRO\Tickets\GroupingCounter::getSearchTerm($set_group_term, $set_group_option);
            if ($term) {
                $type   = $term['type'];
                $op     = $term['op'];
                $choice = $term;
                unset($choice['type'], $choice['op']);

                $output->writeln("<info>Grouping by {$term['type']} = {$set_group_option}</info>");

                $searcher->addTerm($type, $op, $choice);
            }
        }

        echo "SQL:\n";
        echo str_repeat('-', 72)."\n";
        echo trim(Strings::trimLines($searcher->getSql()));
        echo "\n".str_repeat('-', 72);

        $results_ids = $searcher->getMatches();
        echo "\n\n\nResult IDs (".count($results_ids)."):\n";
        echo str_repeat('-', 72)."\n";
        echo implode(', ', $searcher->getMatches());
        echo "\n".str_repeat('-', 72);
        echo "\n\n\n";

        if ($input->getOption('ticketlist')) {
            $tickets = $container->getEm()->getRepository('DeskPRO:Ticket')->getByIds($results_ids, true);
            echo 'Ticket List ('.count($tickets)."):\n";
            echo str_repeat('-', 72)."\n";
            foreach ($tickets as $t) {
                echo sprintf("Ticket #%-8d: %s\n", $t->id, $t->subject);
            }
            echo str_repeat('-', 72);
        }

        echo "\n";

        return 0;
    }
}
