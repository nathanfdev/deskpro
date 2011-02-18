<?php

namespace Application\DevBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

class TestController extends Controller
{
    public function indexAction()
    {
		echo App::getSetting('core.deskpro_url');
		exit;
		$all_triggers = App::getEntityRepository('DeskPRO:TicketTrigger')->getTriggersForEvents(array('new_reply'));
		var_dump(count($all_triggers));

		$trigger = App::getEntityRepository('DeskPRO:TicketTrigger')->find(1);
		$ticket = App::getEntityRepository('DeskPRO:Ticket')->find(3);

		print_r($trigger->getEditActions($ticket));

		exit;
		$plugin = new \Application\DeskPRO\Entity\Plugin();
		$plugin['plugin_callback'] = 'Application\\DeskPRO\\Plugin\\TicketTrigger\\SayCampfire::sayEvent';
		$plugin['callback_options'] = array(
			'campfire_account_name' => 'deskpro',
			'campfire_room_id' => '43826',
			'campfire_api_token' => '8fda7258703a5a2d9ce76dfc87f9f7a46b26f7a7'
		);
		\Application\DeskPRO\App::getOrm()->persist($plugin);
		\Application\DeskPRO\App::getOrm()->flush();


		$trigger = new \Application\DeskPRO\Entity\TicketTrigger();
		$trigger['title'] = 'Announce to campfire';
		$trigger['event_trigger'] = 'new_reply';
		$trigger['actions'] = array(
			array('rule_type' => 'trigger_plugin', 'plugin_id' => 1)
		);
		\Application\DeskPRO\App::getOrm()->persist($trigger);
		\Application\DeskPRO\App::getOrm()->flush();

		exit;
    }
}
