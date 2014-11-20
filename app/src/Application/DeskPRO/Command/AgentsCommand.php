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

/**
* DeskPRO
*
* @package DeskPRO
*/

namespace Application\DeskPRO\Command;

use Application\DeskPRO\Entity;
use Orb\Util\Strings;
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
        $this->addArgument('action', InputArgument::OPTIONAL, 'The action to perform: reset-password, make-admin, make-billing, whitelist-ip', 'list');
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
     * @param  string                                  $caption
     * @return \Application\DeskPRO\Entity\Person|null
     */
    private function askForAgent($caption)
    {
        $helper = $this->getHelper('dialog');

        $email = $helper->ask($this->output, "$caption> ", '');
        $agent = $this->getEm()->getRepository('DeskPRO:Person')->findOneByEmail($email);

        if (!$agent || !$agent->can_agent) {
            $this->output->writeln("<error>There is no agent with that email address.</error>");

            return null;
        }

        return $agent;
    }

    /**
     * @param  string                                  $caption
     * @return \Application\DeskPRO\Entity\Person|null
     */
    private function getAgentFromInput($caption)
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

            if (!$agent || !$agent->can_agent) {
                if ($input_id) {
                    $this->output->writeln("<error>There is no agent with that ID.</error>");
                }
                if ($input_email) {
                    $this->output->writeln("<error>There is no agent with that email address.</error>");
                }

                return null;
            }

            $this->output->writeln("Agent: {$agent->display_name} <$agent->email_address>");

            return $agent;
        } else {
            return $this->askForAgent($caption);
        }
    }

    /**
     * @param  InputInterface  $input
     * @param  OutputInterface $output
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
            case 'make-billing':
                return $this->makeBillingAction();
            case 'whitelist-ip':
                return $this->whitelistIpAction();
            case 'list':
                return $this->listAction();
            default:
                $output->writeln("The following actions are supported:");
                $output->writeln("\treset-password");
                $output->writeln("\tmake-admin");
                $output->writeln("\tmake-billing");
                $output->writeln("\twhitelist-ip");
                $output->writeln("\tlist");

                return 1;
        }
    }

    /**
     * @return int
     */
    private function listAction()
    {
        $agents = $this->getContainer()->getEm()->getRepository('DeskPRO:Person')->getAgents();

        $table = array();

        foreach ($agents as $a) {
            $table[] = array(
                $a->id,
                $a->display_name,
                $a->email_address,
                $a->can_admin ? '*' : '',
            );
        }

        echo Strings::asciiTable($table, array('ID', 'Name', 'Email Address', 'Admin'));
        echo "\n";

        echo "Use this command with the --help option to see what other actions you can perform.\n";

        return 0;
    }

    /**
     * @return int
     */
    private function resetPasswordAction()
    {
        $agent = $this->getAgentFromInput("Enter the email address of the agent to reset the password for");
        if (!$agent) {
            return 1;
        }

        if ($this->input->getOption('value')) {
            $new_pass = trim($this->input->getOption('value'));
        } else {
            $new_pass = $this->getHelper('dialog')->ask($this->output, "Enter the password to set> ", '');
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
    private function makeAdminAction()
    {
        $agent = $this->getAgentFromInput("Enter the email address of the agent you want to promote to admin");
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
        $agent = $this->getAgentFromInput("Enter the email address of the agent you want to give billing permission to");
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
            $this->output->writeln("IP Security is not enabled for your helpdesk.");

            return 0;
        }

        $agent = $this->getAgentFromInput("Enter the email address of the agent to whitelist an IP for");
        if (!$agent) {
            return 1;
        }

        $repo = $this->getContainer()->getEm()->getRepository('DeskPRO:WhiteListedIP');

        if ($this->input->getOption('value')) {
            $ip_address = trim($this->input->getOption('value'));
        } else {
            $ip_address = $this->getHelper('dialog')->ask($this->output, "Enter the IP address to whitelist> ", '');
        }

        $existing_ips = $repo->getIpsForPerson($agent);

        if (in_array($ip_address, $existing_ips)) {
            $this->output->writeln($ip_address." is already whitelisted for {$agent->display_name} <$agent->email_address>");

            return 1;
        }

        $whitelisted_ip                  = new Entity\WhiteListedIp();
        $whitelisted_ip['person']        = $agent;
        $whitelisted_ip['ip_address']    = $ip_address;

        $this->getContainer()->getEm()->persist($whitelisted_ip);
        $this->getContainer()->getEm()->flush();

        $this->output->writeln("$ip_address has been whitelisted for {$agent->display_name} <$agent->email_address>");

        return 0;
    }
}
