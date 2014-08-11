<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

use Application\DeskPRO\App;
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

	protected function configure()
	{
		$this->setName('dp:agents');
		$this->addOption('reset-password', null, InputOption::VALUE_NONE, 'Reset the password of an admin');
		$this->addOption('make-admin', null, InputOption::VALUE_NONE, 'Turn an agent into an admin');
		$this->addOption('make-billing', null, InputOption::VALUE_NONE, 'Turn an agent into a user with billing permission');
		$this->addArgument('email', InputArgument::OPTIONAL, 'Agent email address or ID', null);
		$this->addOption('whitelist-ip', null, InputOption::VALUE_NONE, 'Whitelist an IP for an agent');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$input_email = $input->getArgument('email');

		$helper = $this->getHelper('dialog');
		$em     = $this->getContainer()->getEm();

		$find_agent = function($caption) use ($helper, $em, $output) {
			$email = $helper->ask($output, "$caption> ", '');
			$agent = $em->getRepository('DeskPRO:Person')->findOneByEmail($email);

			if (!$agent || !$agent->can_agent) {
				$output->writeln("<error>There is no agent with that email address.</error>");
				return null;
			}

			return $agent;
		};

		$get_agent = function($caption) use ($find_agent, $input_email, $em, $output) {
			if ($input_email) {
				$agent = $em->getRepository('DeskPRO:Person')->findOneByEmail($input_email);

				if (!$agent || !$agent->can_agent) {
					$output->writeln("<error>There is no agent with that email address.</error>");
					return null;
				}

				return $agent;
			} else {
				return $find_agent($caption);
			}
		};

		if (!$input->getOption('reset-password') && !$input->getOption('make-admin') && !$input->getOption('make-billing')) {
			$agents = $this->getContainer()->getEm()->getRepository('DeskPRO:Person')->getAgents();

			$table = array();

			foreach ($agents as $a) {
				$table[] = array(
					$a->id,
					$a->display_name,
					$a->email_address,
					$a->can_admin ? '*' : ''
				);
			}

			echo Strings::asciiTable($table, array('ID', 'Name', 'Email Address', 'Admin'));
			echo "\n";

			return 0;

		} elseif ($input->getOption('reset-password')) {

			$agent = $get_agent("Enter the email address of the agent to reset the password for");
			if (!$agent) {
				return 1;
			}

			$new_pass = $this->getHelper('dialog')->ask($output, "Enter the password to set> ", '');
			$agent->setPassword($new_pass);

			$this->getContainer()->getEm()->persist($agent);
			$this->getContainer()->getEm()->flush();

			$output->writeln("The password for {$agent->display_name} <$agent->email_address> has been reset.");

			return 0;

		} elseif ($input->getOption('make-admin')) {
			$agent = $get_agent("Enter the email address of the agent you want to promote to admin");
			if (!$agent) {
				return 1;
			}

			if ($agent->can_admin) {
				$output->writeln("Agent is already an admin");
				return 0;
			}

			$agent->can_admin = true;
			$this->getContainer()->getEm()->persist($agent);
			$this->getContainer()->getEm()->flush();

			$output->writeln("{$agent->display_name} <$agent->email_address> has been promoted to admin");
			return 0;

		} elseif ($input->getOption('make-billing')) {
			$agent = $get_agent("Enter the email address of the agent you want to give billing permission to");
			if (!$agent) {
				return 1;
			}

			if ($agent->can_billing) {
				$output->writeln("Agent already has billing permission");
				return 0;
			}

			$agent->can_admin = true;
			$this->getContainer()->getEm()->persist($agent);
			$this->getContainer()->getEm()->flush();

			$output->writeln("{$agent->display_name} <$agent->email_address> has been given billing permissions");
			return 0;

		} elseif ($input->getOption('whitelist-ip')) {
			if (!$this->container->getSetting('agent.ip_security.enabled')) {
				$output->writeln("IP Security is not enabled for your helpdesk.");
				return 0;
			}
			
			$agent = $find_agent("Enter the email address of the agent to whitelist an IP for");
			if (!$agent) {
				return 1;
			}
			
			$repo = $this->getContainer()->getEm()->getRepository('DeskPRO:WhiteListedIP');
			
			$ip_address = $this->getHelper('dialog')->ask($output, "Enter the IP address to whitelist> ", '');
			
			$existing_ips = $repo->getIpsForPerson($agent);
			
			if (in_array($ip_address, $existing_ips)) {
				$output->writeln($ip_address . " is already whitelisted for this agent");
				return 1;
			}
			
			$whitelisted_ip = new Entity\WhiteListedIp();
			$whitelisted_ip['person']	= $agent;
			$whitelisted_ip['ip_address']	= $ip_address;
			
			$this->getContainer()->getEm()->persist($whitelisted_ip);
			$this->getContainer()->getEm()->flush();

			$output->writeln("$ip_address has been whitelisted for {$agent->display_name} <$agent->email_address>");
			return 0;

		} else {
			$output->writeln("Use --help to see available commands");
			return 0;
		}
	}
}
