<?php

namespace Application\AgentBundle\Controller;

use \Application\DeskPRO\Entity;
use \Application\DeskPRO\App;
use \Orb\Util\Arrays;

class TestController extends AbstractController
{
    public function indexAction()
    {
		$department = App::getEntityRepository('DeskPRO:Department')->find(1);

		$rule = new Entity\CustomDefTicketRule();
		$rule['department'] = $department;
		$rule->addCategoryCondition(1);
		$rule->addCategoryCondition(5);

		$rule->addProductCondition(1);
		$rule->addProductCondition(2);
		$rule->addProductCondition(3);

		$rule->addHideFieldAction(1);
		$rule->addHideFieldAction(2);
		$rule->addShowFieldAction(1);
		$rule->addStopRulesAction();

		//App::getOrm()->persist($rule);
		//App::getOrm()->flush();

		exit;
    }
}
