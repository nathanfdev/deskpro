<?php

namespace Application\AgentBundle\Controller;

use \Application\DeskPRO\App;

class TestController extends AbstractController
{
    public function indexAction()
    {
		echo '<pre>';

		$ticket = App::getEntityRepository('DeskPRO:Ticket')->find(3);

		$labels = array('test', 'test2');
		$labels = array('test');
		$ticket->getLabelManager()->setLabelsArray($labels);

		foreach ($ticket['labels'] as $label) {
			echo $label['label'] . "\n";
		}

		App::getOrm()->persist($ticket);
		App::getOrm()->flush();

		exit;
    }
}
