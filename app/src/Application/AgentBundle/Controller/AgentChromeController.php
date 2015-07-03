<?php

namespace Application\AgentBundle\Controller;

class AgentChromeController extends AbstractController
{
    public function requireRequestToken($action, $arguments = null)
    {
        if ($action == 'agentChromeAction') {
            return false;
        }

        return parent::requireRequestToken($action, $arguments);
    }

    public function agentChromeAction()
    {
        return $this->render('AgentBundle:AgentChrome:agent-window.html.twig');
    }

    public function loadSessionAction()
    {
        $data = array(

        );

        return $this->createJsonResponse($data);
    }
}