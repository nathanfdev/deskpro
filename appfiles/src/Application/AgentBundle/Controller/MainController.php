<?php

namespace Application\AgentBundle\Controller;

class MainController extends AbstractController
{
    public function indexAction()
    {
        return $this->render('AgentBundle:Main:index.twig');
    }

	public function settingsAction()
    {
        return $this->render('AgentBundle:Main:settings.twig');
    }

	public function settingsTicketMacrosAction()
    {
        return $this->render('AgentBundle:Main:settings-ticket-macros.twig');
    }
}
