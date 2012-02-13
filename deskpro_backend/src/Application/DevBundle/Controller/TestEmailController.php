<?php

namespace Application\DevBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\FeedbackCategory;

use Orb\Util\Strings;

class TestEmailController extends Controller
{
    public function userNewTicketAction()
    {
		$ticket = App::getEntityRepository('DeskPRO:Ticket')->find(3);
		$new_message = $ticket->messages[0];

		return $this->render("DeskPRO:emails_user:new-ticket.html.twig", array(
			'ticket' => $ticket,
			'new_message' => $new_message,
			'settings' => App::get(App::SERVICE_SETTINGS)
		));
    }
}
