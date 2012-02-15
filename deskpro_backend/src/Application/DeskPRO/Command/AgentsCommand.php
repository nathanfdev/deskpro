<?php

namespace Application\DeskPRO\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Log\Logger;

use Orb\Util\Util;
use Orb\Util\Numbers;

class AgentsCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	/**
	 * @var \Application\DeskPRO\Log\Logger
	 */
	protected $logger;

	protected function configure()
	{
		$this->setName('dp:agents');
		$this->addOption('show-agents', null, InputOption::VALUE_NONE, 'List all agents');
		$this->addOption('reset-password', null, InputOption::VALUE_NONE, 'Reset the password of an admin');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		if ($input->getOption('show-agents')) {
			$agents = $this->getContainer()->getEm()->getRepository('DeskPRO:Person')->getAgents();

			$output->writeln("ADMINS");
			$output->writeln("===========");

			foreach ($agents as $a) {
				if ($a->can_admin) {
					echo "{$a->display_name} <$a->email_address>\n";
				}
			}
			echo "\n";

			$output->writeln("AGENTS");
			$output->writeln("===========");

			foreach ($agents as $a) {
				if (!$a->can_admin) {
					echo "{$a->display_name} <$a->email_address>\n";
				}
			}

			return 0;

		} elseif ($input->getOption('reset-password')) {
			$email = $this->getHelper('dialog')->ask($output, "Enter the email address of the agent to reset the password for> ", '');
			$agent = $this->getContainer()->getEm()->getRepository('DeskPRO:Person')->findOneByEmail($email);

			if (!$agent || !$agent->can_agent) {
				$output->writeln("<error>There is no agent with that email address.</error>");
				return 1;
			}

			$new_pass = $this->getHelper('dialog')->ask($output, "Enter the password to set> ", '');
			$agent->setPassword($new_pass);

			$this->getContainer()->getEm()->persist($agent);
			$this->getContainer()->getEm()->flush();

			$output->writeln("The password for {$agent->display_name} <$agent->email_address> has been reset.");

			return 0;
		} else {
			$output->writeln("Use --help to see available comamnds");
			return 0;
		}
	}
}
