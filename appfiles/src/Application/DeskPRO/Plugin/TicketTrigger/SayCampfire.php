<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Plugin\TicketTrigger;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Strings;
use Orb\Util\Arrays;

class SayCampfire
{
	public static function sayEvent(array $info)
	{
		$ticket = $info['ticket'];
		$logs = $info['logs'];

		$router = App::getRouter();
		$deskpro_url = App::getSetting('core.deskpro_url');
		$ticket_url = "{$deskpro_url}agent/#ticket-{$ticket['id']}";

		if (isset($logs['ticket_created'])) {
			$say = "New ticket created: {$ticket['subject']} $ticket_url";
		} elseif (isset($logs['message_created'])) {
			$message = $logs['message_created']->getMessage();
			$say = "{$message['person']['display_name']} replied to ticket {$ticket['subject']} $ticket_url";
		} else {
			return; // dont know how to handle the event then
		}

		$resource = "https://{$info['campfire_account_name']}.campfirenow.com/room/{$info['campfire_room_id']}/speak.json";
		$payload = json_encode(array('message' => array('type' => 'TextMessage', 'body' => $say)));

		$ch = curl_init($resource);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		curl_setopt($ch, CURLOPT_USERPWD, $info['campfire_api_token'].':x');
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_USERAGENT, 'DeskPRO Campfire Announce');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);

		$contents = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
	}
}