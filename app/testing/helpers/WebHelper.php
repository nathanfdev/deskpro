<?php
namespace Codeception\Module;

use Application\DeskPRO\Entity\Session;
use Orb\Util\Util;

class WebHelper extends \Codeception\Module
{
	/**
	 * Starts a new admin
	 */
	public function startAgentSession($agent_email)
	{
		$container = $this->getDpControlHelper()->getSymfonyContainer();
		$db = $container->getDb();

		$agent_id = $db->fetchColumn("
			SELECT people.id
			FROM people
			LEFT JOIN people_emails ON (people_emails.person_id = people.id)
			WHERE people.is_agent = 1 AND people_emails.email = ?
			LIMIT 1
		", array($agent_email));

		session_start();
		$_SESSION = array(
			'_sf2_attributes' => array(
				'dp_interface' => 'admin',
				'auth_person_id' => $agent_id,
			),
			'_sf2_flashes' => array(),
			'_sf2_meta' => array()
		);

		$db->executeUpdate("
			INSERT INTO `sessions` (`person_id`, `visitor_id`, `auth`, `interface`, `user_agent`, `ip_address`, `data`, `is_person`, `is_bot`, `is_helpdesk`, `active_status`, `is_chat_available`, `date_created`, `date_last`)
			VALUES (
				$agent_id,
				NULL,
				'HDJWW7T8CWRZ2NN',
				'agent',
				'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.26 Safari/537.36',
				'192.168.4.2',
				'".session_encode()."',
				1,
				0,
				1,
				'',
				0,
				'".date('Y-m-d H:i:s')."',
				'".date('Y-m-d H:i:s')."'
			)
		");

		$id = $db->lastInsertId();

		$id_enc = Util::baseEncode($id, Util::BASE36_ALPHABET) . '-HDJWW7T8CWRZ2NN';
		$this->getModule('WebDriver')->resetCookie('dpsid-agent');
		$this->getModule('WebDriver')->setCookie('dpsid-agent', $id_enc);
	}


	/**
	 * @return \Codeception\Module\DpControlHelper
	 */
	public function getDpControlHelper()
	{
		return $this->getModule('DpControlHelper');
	}
}
