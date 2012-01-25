<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Import
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Import\Importer\Step\Deskpro3;

use Application\DeskPRO\Entity\EmailGateway;
use Application\DeskPRO\Entity\EmailGatewayAddress;

class PopAccountsStep extends AbstractDeskpro3Step
{
	public static function getTitle()
	{
		return 'Import POP3 Accounts';
	}

	public function run()
	{
		$count = $this->getOldDb()->fetchAll("SELECT COUNT(*) FROM gateway_pop_accounts");
		$this->logMessage(sprintf("Importing %d POP3 accounts", $count));
		if (!$count) {
			return;
		}

		$accounts = $this->getOldDb()->fetchAll("SELECT * FROM gateway_pop_accounts WHERE target = 'user' ORDER BY id ASC");

		$start_time = microtime(true);

		$this->getDb()->beginTransaction();

		try {
			foreach ($accounts as $account) {
				$this->processAccount($account);
			}

			$this->getDb()->commit();
		} catch (\Exception $e) {
			$this->getDb()->rollback();
			throw $e;
		}

		$end_time = microtime(true);
		$this->logMessage(sprintf("Done all accounts. Took %.3f seconds.", $end_time-$start_time));
	}

	protected function processAccount(array $account)
	{
		#------------------------------
		# Make sure we havent already done them
		#------------------------------

		$check_exist = $this->getMappedNewId('gateway_account', $account['id']);
		if ($check_exist) {
			$this->getLogger()->log("{$account['id']} already mapped, skipping", 'DEBUG');
			return;
		}

		#------------------------------
		# Create it
		#------------------------------

		$new_gateway = new EmailGateway();
		$new_gateway->title = "{$account['server']} :: {$account['username']}";
		$new_gateway->connection_type = EmailGateway::CONN_POP3;
		$new_gateway->connection_options = array(
			'port' => !empty($account['port']) ? $account['port'] : '110',
			'host' => $account['server'],
			'username' => $account['username'],
			'password' => $account['password'],
			'secure' => $account['usessl'] ? true : false
		);
		$new_gateway->gateway_type = 'tickets';
		$new_gateway->is_enabled = $account['active'] ? true : false;

		$this->getEm()->persist($new_gateway);
		$this->getEm()->flush();

		$this->saveMappedId('gateway_account', $account['id'], $new_gateway->id);
	}
}
