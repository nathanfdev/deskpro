<?php

namespace DeskPRO\Bundle\MessengerBundle\Common;

use Application\DeskPRO\People\PersonGuest;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait TraitUserGet
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @return object|null
     */
    protected function getUser()
    {
        $user = null;

        if ($this->container->has('security.token_storage')) {
            $token = $this->container->get('security.token_storage')->getToken();
            if (!$token || !\is_object($user = $token->getUser())) {
                // e.g. anonymous authentication
                $user = null;
            }
        }
        // find user in JWT token in headers
        if (!$user) {
            $request = $this->container->get('request');
            if ($request->headers->has('X-JWT-TOKEN')) {
                if ($jwt = $request->headers->get('X-JWT-TOKEN')) {
                    $user = $this->container->get('widget_jwt_decoder')->getPersonFromJwtPayload($jwt);
                }
            }
        }

        return $user;
    }

    protected function getUserOrGuest()
    {
        return $this->getUser() ?: new PersonGuest();
    }
}
