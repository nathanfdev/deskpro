<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Command;

use Application\DeskPRO\Entity;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AgentsCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    /**
     * @var \Application\DeskPRO\Log\Logger
     */
    protected $logger;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    protected function configure()
    {
        $this->setName('dp:agents');
        $this->addArgument('action', InputArgument::OPTIONAL, 'The action to perform: reset-password, make-admin, make-agent, make-billing, whitelist-ip', 'list');
        $this->addOption('list', 'l', InputOption::VALUE_NONE, 'When a value is not specified as a CLI option, show a list of agents in the system for reference.');
        $this->addOption('value', 'u', InputOption::VALUE_REQUIRED, 'Optionally supply the value to set (for reset-password or whitelist-ip)', null);
        $this->addOption('agent-email', 'm', InputOption::VALUE_REQUIRED, 'Agent email address', null);
        $this->addOption('agent-id', 'd', InputOption::VALUE_REQUIRED, 'Agent ID', null);
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEm()
    {
        return $this->getContainer()->getEm();
    }

    /**
     * @param string $caption
     * @param bool   $require_agent
     *
     * @return \Application\DeskPRO\Entity\Person|null
     */
    private function askForAgent($caption, $require_agent = true)
    {
        $helper = $this->getHelper('dialog');

        $caption = trim($caption);
        if (strlen($caption) > 40) {
            $caption .= "\n";
        }

        $email = $helper->ask($this->output, "$caption> ", '');
        $agent = $this->getEm()->getRepository('DeskPRO:Person')->findOneByEmail($email);

        if (!$agent || ($require_agent && !$agent->can_agent)) {
            $this->output->writeln('<error>There is no person with that email address.</error>');

            return;
        }

        return $agent;
    }

    /**
     * @param string $caption
     * @param bool   $require_agent
     *
     * @return \Application\DeskPRO\Entity\Person|null
     */
    private function getAgentFromInput($caption, $require_agent = true)
    {
        $input_id    = $this->input->getOption('agent-id');
        $input_email = $this->input->getOption('agent-email');

        if ($input_email || $input_id) {
            $agent = null;

            if ($input_id) {
                $agent = $this->getEm()->getRepository('DeskPRO:Person')->find($input_id);
            }

            if ((!$agent || !$agent->can_agent) && $input_email) {
                $agent = $this->getEm()->getRepository('DeskPRO:Person')->findOneByEmail($input_email);
            }

            if (!$agent || ($require_agent && !$agent->can_agent)) {
                if ($input_id) {
                    $this->output->writeln('<error>There is no person with that ID.</error>');
                }
                if ($input_email) {
                    $this->output->writeln('<error>There is no person with that email address.</error>');
                }

                return;
            }

            $this->output->writeln("Agent: {$agent->display_name} <$agent->email_address>");

            return $agent;
        } else {
            if ($this->input->getOption('list')) {
                $this->listAction(true);
            }

            return $this->askForAgent($caption, $require_agent);
        }
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;

        switch ($input->getArgument('action')) {
            case 'reset-password':
                return $this->resetPasswordAction();
            case 'make-admin':
                return $this->makeAdminAction();
            case 'make-agent':
                return $this->makeAgentAction();
            case 'make-billing':
                return $this->makeBillingAction();
            case 'whitelist-ip':
                return $this->whitelistIpAction();
            case 'list':
                return $this->listAction();
            default:
                $output->writeln('The following actions are supported:');
                $output->writeln("\treset-password");
                $output->writeln("\tmake-admin");
                $output->writeln("\tmake-billing");
                $output->writeln("\twhitelist-ip");
                $output->writeln("\tlist");

                return 1;
        }
    }

    /**
     * @param bool $asReference
     *
     * @return int
     */
    private function listAction($asReference = false)
    {
        $agents = $this->getContainer()->getEm()->getRepository('DeskPRO:Person')->getAgents();

        $table = new Table($this->output);
        $table->setHeaders(['ID', 'Name', 'Email Address', 'Admin']);

        foreach ($agents as $a) {
            $table->addRow([
                $a->id,
                $a->display_name,
                $a->email_address,
                $a->can_admin ? '*' : '',
            ]);
        }

        $table->render();

        if (!$asReference) {
            echo "\n";
            echo "Use this command with the --help option to see what other actions you can perform.\n";
        }

        return 0;
    }

    /**
     * @return int
     */
    private function resetPasswordAction()
    {
        $agent = $this->getAgentFromInput('Enter the email address of the agent to reset the password for');
        if (!$agent) {
            return 1;
        }

        if ($this->input->getOption('value')) {
            $new_pass = trim($this->input->getOption('value'));
        } else {
            $new_pass = $this->getHelper('dialog')->ask($this->output, 'Enter the password to set> ', '');
        }
        $agent->setPassword($new_pass);

        $this->getContainer()->getEm()->persist($agent);
        $this->getContainer()->getEm()->flush();

        $this->output->writeln("The password for {$agent->display_name} <$agent->email_address> has been reset.");

        return 0;
    }

    /**
     * @return int
     */
    private function makeAgentAction()
    {
        $agent = $this->getAgentFromInput('Enter the email address of the user you want to promote to an agent', false);
        if (!$agent) {
            return 1;
        }

        if ($agent->is_agent) {
            $this->output->writeln("{$agent->display_name} <$agent->email_address> is already an agent");

            return 0;
        }

        $agent->is_agent  = true;
        $agent->can_admin = true;
        $this->getContainer()->getEm()->persist($agent);
        $this->getContainer()->getEm()->flush();

        $this->output->writeln("{$agent->display_name} <$agent->email_address> has been promoted to agent");

        return 0;
    }

    /**
     * @return int
     */
    private function makeAdminAction()
    {
        $agent = $this->getAgentFromInput('Enter the email address of the agent you want to promote to admin');
        if (!$agent) {
            return 1;
        }

        if ($agent->can_admin) {
            $this->output->writeln("{$agent->display_name} <$agent->email_address> is already an admin");

            return 0;
        }

        $agent->can_admin = true;
        $this->getContainer()->getEm()->persist($agent);
        $this->getContainer()->getEm()->flush();

        $this->output->writeln("{$agent->display_name} <$agent->email_address> has been promoted to admin");

        return 0;
    }

    /**
     * @return int
     */
    private function makeBillingAction()
    {
        $agent = $this->getAgentFromInput('Enter the email address of the agent you want to give billing permission to');
        if (!$agent) {
            return 1;
        }

        if ($agent->can_billing) {
            $this->output->writeln("{$agent->display_name} <$agent->email_address> already has billing permission");

            return 0;
        }

        $agent->can_admin = true;
        $this->getContainer()->getEm()->persist($agent);
        $this->getContainer()->getEm()->flush();

        $this->output->writeln("{$agent->display_name} <$agent->email_address> has been given billing permissions");

        return 0;
    }

    /**
     * @return int
     */
    private function whitelistIpAction()
    {
        if (!$this->getContainer()->getSetting('agent.ip_security.enabled')) {
            $this->output->writeln('IP Security is not enabled for your helpdesk.');

            return 0;
        }

        $agent = $this->getAgentFromInput('Enter the email address of the agent to whitelist an IP for');
        if (!$agent) {
            return 1;
        }

        $repo = $this->getContainer()->getEm()->getRepository('DeskPRO:WhiteListedIp');

        if ($this->input->getOption('value')) {
            $ip_address = trim($this->input->getOption('value'));
        } else {
            $ip_address = $this->getHelper('dialog')->ask($this->output, 'Enter the IP address to whitelist> ', '');
        }

        $existing_ips = $repo->getIpsForPerson($agent);

        if (in_array($ip_address, $existing_ips)) {
            $this->output->writeln($ip_address." is already whitelisted for {$agent->display_name} <$agent->email_address>");

            return 1;
        }

        $whitelisted_ip               = new Entity\WhiteListedIp();
        $whitelisted_ip['person']     = $agent;
        $whitelisted_ip['ip_address'] = $ip_address;

        $this->getContainer()->getEm()->persist($whitelisted_ip);
        $this->getContainer()->getEm()->flush();

        $this->output->writeln("$ip_address has been whitelisted for {$agent->display_name} <$agent->email_address>");

        return 0;
    }
}
