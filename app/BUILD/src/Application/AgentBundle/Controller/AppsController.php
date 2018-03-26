<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\App\Native\RequestHandler\AgentRequestContext;

class AppsController extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    public function requireRequestToken($action, $arguments = null)
    {
        return false;
    }

    public function runAction($app_id, $action = 'default')
    {
        $manager = $this->container->getAppManager();

        try {
            $native_app = $manager->getNativeApp($app_id);
        } catch (\InvalidArgumentException $e) {
            throw $this->createNotFoundException($e->getMessage());
        }

        $handler_class = $native_app->getConfig()->getAgentRequestHandlerClass();
        if (!$handler_class) {
            throw $this->createNotFoundException('Bad handler class');
        }

        $context = new AgentRequestContext(
            $this->container,
            $this->request,
            $this,
            $native_app,
            $this->person,
            $action
        );

        $handler = new $handler_class();
        $result  = $handler->handleAgentRequest($context);

        return $result;
    }
}
