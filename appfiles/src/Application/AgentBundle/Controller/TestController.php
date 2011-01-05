<?php

namespace Application\AgentBundle\Controller;

class TestController extends AbstractController
{
    public function indexAction()
    {
		echo $this->person['id'];
		
		$grouper = new \Application\DeskPRO\Tickets\GroupingCounter();
		$grouper->setGrouping('department_id');
		$grouper->setMode('unassigned');

		$data = $grouper->getDisplayArray();

		echo '<pre>';
		print_r($data);

		exit;
    }
}
