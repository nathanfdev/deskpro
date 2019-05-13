<?php

namespace DeskPRO\Bundle\AppBundle\Command\Debug;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Sla;
use Application\DeskPRO\Searcher\TicketSearch;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class QuerySlaCommand extends ContainerAwareCommand
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('dp:debug:query:sla')
            ->setDescription('Debug SLA query and results')
            ->addOption('sla-id', null, InputOption::VALUE_REQUIRED, 'SLA Id')
            ->addOption('agent-id', null, InputOption::VALUE_REQUIRED, 'Agent Id')
            ->addOption('sla-status', null, InputOption::VALUE_REQUIRED, 'SLA status (ok|warning|fail)')
            ->addOption('sql', null, InputOption::VALUE_NONE, 'Show SQL')
        ;
    }

    /**
     * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    public function getContainer()
    {
        return parent::getContainer();
    }

    public function getEm()
    {
        return $this->getContainer()->getEm();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $slaId     = $input->getOption('sla-id');
        $agentId   = $input->getOption('agent-id');
        $slaStatus = $input->getOption('sla-status');
        $showSql   = $input->getOption('sql');

        if (!$slaId or !$agentId) {
            $output->writeln('<error>Please provide --sla-id and --agent-id options</error>');

            return 1;
        }

        $sql = $this->getSlaQuery($slaId, $agentId, $slaStatus);
        if ($showSql) {
            $output->writeln($sql);
        } else {
            $ticketIds = $this->getEm()->getConnection()->fetchAllCol($sql);
            foreach ($ticketIds as $ticketId) {
                $output->writeln($ticketId);
            }
            $output->writeln(sprintf('<info>Count: %s</info>', count($ticketIds)));
        }

        return 0;
    }

    protected function getSlaQuery($slaId, $agentId, $slaStatus)
    {
        $sla = $this->getEm()->getRepository(Sla::class)->find($slaId);
        if (!$sla) {
            throw new \Exception("Can't find sla");
        }

        $agent = $this->getEm()->getRepository(Person::class)->find($agentId);
        if (!$agent) {
            throw new \Exception("Can't find agent");
        }
        if (!$agent->isAgent()) {
            $this->output->writeln('<info>User is not an agent</info>');
        }

        $searcher = new TicketSearch();
        $searcher->setPerson($agent);

        $sla_filter = $agent->getPref('agent.ui.sla.ticket-filter', 'all');
        if ($sla_filter == 'agent') {
            $searcher->addTerm(TicketSearch::TERM_AGENT, 'is', $agent->id);
        } elseif ($sla_filter == 'team') {
            $searcher->addTerm(TicketSearch::TERM_AGENT_TEAM, 'is', $agent->getAgentTeamIds());
        }

        $searcher->addTerm(TicketSearch::TERM_SLA_COMPLETED, 'is', [
            'is_completed' => 0,
            'sla_id'       => $slaId,
        ]);

        if ($slaStatus) {
            $searcher->addTerm(TicketSearch::TERM_SLA_STATUS, 'is', [
                'sla_status' => $slaStatus,
                'sla_id'     => $slaId,
            ]);
        }

        if ($sla->sla_type == \Application\DeskPRO\Entity\Sla::TYPE_WAITING_TIME) {
            $searcher->addTerm(TicketSearch::TERM_STATUS, 'is', 'awaiting_agent');
        } elseif ($sla->sla_type == \Application\DeskPRO\Entity\Sla::TYPE_FIRST_RESPONSE) {
            $searcher->addTerm(TicketSearch::TERM_STATUS, 'is', 'awaiting_agent');
        } else {
            $searcher->addTerm(TicketSearch::TERM_STATUS, 'is', ['awaiting_agent', 'awaiting_user']);
        }

        $order_by = $agent->getPref('agent.ui.ticket-sla-order-by.'.$sla['id'], 'ticket.sla_severity:desc');

        if ($order_by) {
            $searcher->setOrderByCode($order_by);
        }

        // if no sla status was provided then count by sla status
        // to update sla badge counts real-time
        $slaGroupCounts = [];
        if (!$slaStatus) {
            foreach (['ok', 'warning', 'fail'] as $groupStatus) {
                $groupSearcher = clone $searcher;
                $groupSearcher->addTerm(TicketSearch::TERM_SLA_STATUS, 'is', [
                    'sla_status' => $groupStatus,
                    'sla_id'     => $sla->getId(),
                ]);

                $slaGroupCounts[$groupStatus] = $groupSearcher->getCount();
            }
        }

        return $searcher->getSql();
    }
}
