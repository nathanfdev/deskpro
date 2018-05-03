<?php

namespace Application\AgentBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

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
        $env = $this->get('deskpro.app_env');
        if (!($env->getEnvId() === 'dev' || $env->getEnvId() === 'test' || $env->getConfig('settings.enable_agent_v2'))) {
            throw $this->createNotFoundException();
        }

        return $this->render('AgentBundle:AgentChrome:agent-window.html.twig');
    }

    public function loadSessionAction()
    {
        $data = [

        ];

        return $this->createJsonResponse($data);
    }

    public function preActionHandler(Request $request, $action, $arguments = null)
    {
    }
}
