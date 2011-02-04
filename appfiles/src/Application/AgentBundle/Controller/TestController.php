<?php

namespace Application\AgentBundle\Controller;

use \Application\DeskPRO\Entity;
use \Application\DeskPRO\App;
use \Orb\Util\Arrays;

class TestController extends AbstractController
{
    public function indexAction()
    {

		$widget = new Entity\Widget();
		$widget['name_id'] = 'com_deskpro_campfire_announce';
		$widget['section'] = 'agent.ticket';
		$widget['js_widget_class'] = 'DeskPRO.Widget.CampfireAnnounce';
		$widget['template_name'] = 'DeskPRO:Widgets:com_deskpro_campfire_announce.twig.html';
		$widget['data'] = array(
			'tab_title' => 'My Widget',
			'api_info' => array(
				'authToken' => 'xxx',
				'roomId' => '374745',
				'accountName' => 'deskpro'
			)
		);

		App::getOrm()->persist($widget);
		App::getOrm()->flush();

		exit;
    }
}
