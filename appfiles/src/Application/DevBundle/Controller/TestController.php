<?php

namespace Application\DevBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity\ClientMessage;

use \Orb\Util\Strings;

class TestController extends Controller
{
    public function indexAction()
    {
		echo App::getTranslator()->phrase('core_chat.msg_unassigned_agent', array('agent_name'=> 'test'));

		exit;
		return $this->render('DevBundle:Test:test.html.twig');
    }
}
