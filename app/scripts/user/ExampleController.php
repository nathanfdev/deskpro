<?php

namespace DpScripts\User;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ExampleController.
 */
class ExampleController extends BaseController
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function helloAction(Request $request)
    {
        $user = $this->getUser();

        if ($user) {
            return new Response("Hello, {$user->getName()}");
        } else {
            return new Response('Hello, world');
        }
    }
}
