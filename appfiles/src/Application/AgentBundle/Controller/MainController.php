<?php

namespace Application\AgentBundle\Controller;

class MainController extends AbstractController
{
    public function indexAction()
    {
        return $this->render('AgentBundle:Main:index.twig');
    }
}
