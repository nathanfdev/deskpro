<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace Application\DeskPRO\Command;

use Application\DeskPRO\App;
use Application\DeskPRO\Email\EmailAccount\EmailAccountManager;
use Application\DeskPRO\EmailGateway\Runner;
use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Log\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CollectEmailCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('dp:collect-email');
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Use the account even if its disabled');
        $this->addOption('time', 't', InputOption::VALUE_REQUIRED, 'Time limit to spend before quitting early (seconds). Defaults to 60 seconds.');
        $this->addOption('only-collect', 'c', InputOption::VALUE_NONE, 'Only collect and save email (with "inserted" state), do not process it.');
        $this->addArgument('accounts', InputArgument::REQUIRED, 'IDs or email addresses of the accounts to process. Separate multiple accounts by commas. Use the special "all" to collect from all accounts.');
    }

    /**
     * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    public function getContainer()
    {
        return parent::getContainer();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $account_manager = App::$container->getEmailAccountManager();
        $only_collect    = $input->getOption('only-collect');

        $accounts = $input->getArgument('accounts') ?: '';
        if (!$accounts) {
            $output->writeln('<error>Please specify at least one account.</error>');

            return 1;
        }

        if ($accounts === 'all') {
            $accounts = $account_manager->getAllActiveAccounts(EmailAccountManager::WITH_FETCHER);
        } else {
            $accounts = array_map('trim', explode(',', $accounts));
        }

        foreach ($accounts as $account_id) {
            if ($account_id instanceof EmailAccount) {
                $account = $account_id;
            } elseif (ctype_digit($account_id)) {
                if (!$account_manager->hasAcccount($account_id)) {
                    $output->writeln("<error>No account with ID $account_id</error>");

                    return 1;
                }

                $account = $account_manager->getAccount($account_id);
            } else {
                $account = $account_manager->findAccountForEmailAddress($account_id);

                if (!$account) {
                    $output->writeln("<error>No account with address $account_id</error>");

                    return 1;
                }
            }

            if ($input->getOption('force') && !$account->is_enabled) {
                $output->writeln("<error>Account $account_id is disabled (use -f if you want to use it anyway)</error>");

                return 1;
            }

            if (!$account->incoming_account) {
                $output->writeln("<error>Account $account_id does not have an incoming account config</error>");

                return 1;
            }

            if ($account->incoming_account->getType() == 'noop') {
                $output->writeln('Note: Account is noop. Nothing to do.');

                return 0;
            }

            $time = intval($input->getOption('time') || 0);
            if (!$time || $time < 1) {
                $time = 60;
            }

            #----------------------------------------
            # Run the gateway collection
            #----------------------------------------

            $output->setVerbosity(3);

            $logger = new Logger();
            $logger->addWriter(new \Orb\Log\Writer\ConsoleOutputWriter($output));
            $logger->addFilter(new \Orb\Log\Filter\SimpleLineFormatter());

            $runner = new Runner();
            $runner->setLogger($logger);
            $runner->setPhpTimeLimit(900);
            $runner->executeAccount($account, $time, $only_collect);

            App::getDb()->update('email_accounts', array('is_read_active' => 0), array('id' => $account->getId()));
        }

        return 0;
    }
}
