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
		$widget['name_id'] = 'com_deskpro_example';
		$widget['section'] = 'agent.ticket';
		$widget['js_widget_class'] = 'com_deskpro_example';
		$widget['template_name'] = 'DeskPRO:Widgets:com_deskpro_example.twig.html';
		$widget['data'] = array('tab_title' => 'My Widget');

		App::getOrm()->persist($widget);
		App::getOrm()->flush();

		exit;
    }
}
