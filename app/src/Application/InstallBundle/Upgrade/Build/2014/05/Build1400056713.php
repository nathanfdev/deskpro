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
 * @subpackage
 */

namespace Application\InstallBundle\Upgrade\Build;

use Application\DeskPRO\Entity\EmailAccount;

use Application\DeskPRO\Email\EmailAccount\IncomingAccount;
use Application\DeskPRO\Email\EmailAccount\OutgoingAccount;
use Orb\Util\Arrays;
use Orb\Util\OptionsArray;

class Build1400056713 extends AbstractBuild
{
	public function run()
	{
		$db = $this->container->getDb();
		$em = $this->container->getEm();

		// Reset table
		$db->exec("DELETE FROM email_accounts");
		$db->exec("ALTER TABLE email_accounts AUTO_INCREMENT = 1");

		$this->out("Upgrading email accounts...");

		$gateways      = $db->fetchAllKeyed("SELECT * FROM email_gateways");
		$gateway_addrs = $db->fetchAllGrouped("SELECT * FROM email_gateway_addresses ORDER BY run_order ASC, id ASC", array(), 'email_gateway_id');
		$transports    = $db->fetchAllKeyed("SELECT * FROM email_transports");

		// Save gateway address mapping needed when importing triggers
		$map = array();
		foreach ($gateway_addrs as $gateway_id => $addrs) {
			foreach ($addrs as $a) {
				$map[$a['id']] = $gateway_id;
			}
		}
		$this->saveUpgradeData('201404', 'gateway_address_map', $map);

		$new_accounts = array();
		foreach ($gateways as $gateway) {
			if ($gateway['gateway_type'] != 'tickets') {
				$this->out("Skipping {$gateway['id']}: Must be ticket type");
				continue;
			}

			if (!empty($gateway['linked_transport_id']) && isset($transports[$gateway['linked_transport_id']])) {
				$tr = $transports[$gateway['linked_transport_id']];
			} else {
				$tr = null;
			}

			if (isset($gateway_addrs[$gateway['id']])) {
				$addrs = $gateway_addrs[$gateway['id']];
			} else {
				$addrs = null;
			}

			if (!$addrs) {
				$this->out("Skipping {$gateway['id']}: No addresses");
			}
			if (!$tr) {
				$this->out("Skipping {$gateway['id']}: No transport");
			}

			$new_accounts[$gateway['id']] = $this->_convertGatewayAccount($gateway, $tr, $addrs);
		}

		$default_tr = Arrays::findValue($transports, function($tr) {
			return $tr['match_type'] == 'all';
		});
		$default_account = null;
		if ($default_tr) {
			$default_tr_address = $this->container->getSetting('core.default_from_email');

			$tr_account = new EmailAccount(EmailAccount::TYPE_OUT);
			$tr_account->address = $default_tr_address;
			$tr_account->is_enabled = true;
			$tr_account->outgoing_account = $this->_getTransportConfig($default_tr);

			// Cloud must mark the incoming settings as noop
			if (defined('DPC_IS_CLOUD')) {
				$tr_account->setAccountType(EmailAccount::TYPE_TICKETS);
				$tr_account->incoming_account = new IncomingAccount\NoopConfig();
			}

			$addr_exists = Arrays::findValue($new_accounts, function($account) use ($tr_account) {
				return $account->hasAddress($tr_account->address);
			});

			if (!$addr_exists) {
				$default_account = $tr_account;
			}
		}

		$id_map = array();
		$tmp_id = time();
		foreach ($new_accounts as $want_id => $account) {
			$em->persist($account);
			$em->flush();

			// Update to a high ID that wont collide when we update again below
			$db->executeUpdate('UPDATE email_accounts SET id = ? WHERE id = ?', array($tmp_id, $account->id));
			$id_map[$tmp_id] = $want_id;
			$tmp_id++;
		}

		foreach ($id_map as $tmp_id => $want_id) {
			$db->executeUpdate('UPDATE email_accounts SET id = ? WHERE id = ?', array($want_id, $tmp_id));
		}

		$max_id = $db->fetchColumn("SELECT id FROM email_accounts ORDER BY id DESC LIMIT 1");
		if (!$max_id) $max_id = 0;
		$max_id++;
		$db->exec("ALTER TABLE email_accounts AUTO_INCREMENT = $max_id");

		// Then insert default account with whatever autoinc id is next,
		// we dont care about the id
		if ($default_account) {
			$em->persist($default_account);
			$em->flush();
		}
	}


	/**
	 * @param array $gateway
	 * @param array $tr
	 * @param array $addrs
	 * @return EmailAccount
	 */
	private function _convertGatewayAccount(array $gateway, array $tr = null, array $addrs)
	{
		$account = new EmailAccount(EmailAccount::TYPE_TICKETS);

		if ($gateway['connection_options']) {
			$gateway['connection_options'] = unserialize($gateway['connection_options']);
		} else {
			$gateway['connection_options'] = null;
		}

		$conn_opts = new OptionsArray(!empty($gateway['connection_options']) ? $gateway['connection_options'] : array());

		switch ($gateway['connection_type']) {
			case 'pop3':
				$pop3_config = new IncomingAccount\Pop3Config();
				$pop3_config->host     = $conn_opts->get('host', 'localhost');
				$pop3_config->port     = $conn_opts->get('port', 995);
				$pop3_config->user     = $conn_opts->get('username');
				$pop3_config->password = $conn_opts->get('password');

				if ($conn_opts->get('secure') == 'ssl') {
					$pop3_config->secure_mode = 'ssl';
				} elseif ($conn_opts->get('secure') == 'tls') {
					$pop3_config->secure_mode = 'tls';
				}

				$account->incoming_account = $pop3_config;
				break;

			case 'gmail':
				$gmail_config = new IncomingAccount\GmailConfig();
				$gmail_config->user     = $conn_opts->get('username');
				$gmail_config->password = $conn_opts->get('password');

				$account->incoming_account = $gmail_config;
				break;

			// directory was the type used by cloud accounts
			case 'directory':
				$null_config = new IncomingAccount\NoopConfig();
				$account->incoming_account = $null_config;
				break;

			default:
				throw new \InvalidArgumentException("Unknown account type: {$gateway['connection_type']}");
		}

		$account->outgoing_account = $this->_getTransportConfig($tr);

		if ($gateway['keep_read']) {
			$account->setOption('keep_read', true);
		}

		if (!empty($gateway['date_last_check'])) {
			$account->date_last_incoming = \DateTime::createFromFormat('Y-m-d H:i:s', $gateway['date_last_check']);
		}
		if (!empty($gateway['start_date_limit'])) {
			$account->date_read_start = \DateTime::createFromFormat('Y-m-d H:i:s', $gateway['start_date_limit']);
		}

		$dates = array($account->date_created, $account->date_last_incoming, $account->date_read_start);
		$dates = Arrays::removeFalsey($dates);
		$account->date_created = min($dates);

		$account->address = $addrs[0]['match_pattern'];
		if (isset($addrs[1])) {
			array_shift($addrs);
			$account->other_addresses = array_map(function($a) { return $a['match_pattern']; }, $addrs);
		}

		if (defined('DPC_IS_CLOUD') && $account->other_addresses && !empty($account->other_addresses[0])) {
			$account->setOption('custom_email_address', $account->other_addresses[0]);
		}

		$account->is_enabled = (bool)$gateway['is_enabled'];

		return $account;
	}


	/**
	 * @param array $tr
	 * @return OutgoingAccount\GmailConfig|OutgoingAccount\PhpMailConfig|OutgoingAccount\SmtpConfig
	 * @throws \InvalidArgumentException
	 */
	private function _getTransportConfig(array $tr = null)
	{
		if ($tr === null) {
			$mail_config = new OutgoingAccount\PhpMailConfig();
			return $mail_config;
		}

		if ($tr['transport_options']) {
			$tr['transport_options'] = unserialize($tr['transport_options']);
		} else {
			$tr['transport_options'] = null;
		}

		$conn_opts = new OptionsArray(!empty($tr['transport_options']) ? $tr['transport_options'] : array());

		switch ($tr['transport_type']) {
			case 'mail':
				$mail_config = new OutgoingAccount\PhpMailConfig();
				return $mail_config;

			case 'smtp':
				$smtp_config = new OutgoingAccount\SmtpConfig();
				$smtp_config->host     = $conn_opts->get('host', 'localhost');
				$smtp_config->port     = $conn_opts->get('port', 25);
				$smtp_config->user     = $conn_opts->get('username');
				$smtp_config->password = $conn_opts->get('password');

				if ($conn_opts->get('secure') == 'ssl') {
					$smtp_config->secure_mode = 'ssl';
				} elseif ($conn_opts->get('secure') == 'tls') {
					$smtp_config->secure_mode = 'tls';
				}

				return $smtp_config;

			case 'gmail':
				$gmail_config = new OutgoingAccount\GmailConfig();
				$gmail_config->user     = $conn_opts->get('username');
				$gmail_config->password = $conn_opts->get('password');
				return $gmail_config;

			default:
				throw new \InvalidArgumentException("Unknown mail account: {$tr['transport_type']}");
		}
	}
}