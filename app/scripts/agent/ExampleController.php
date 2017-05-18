<?php

namespace DpScripts\Agent;

use Application\DeskPRO\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ExampleController.
 */
class ExampleController extends AbstractController
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function helloAction(Request $request)
    {
        $user = $this->session->getPerson();

        if ($user && $user->getId()) {
            return new Response("Hello, {$user->getName()}");
        } else {
            return new Response('Hello, world');
        }
    }
}
