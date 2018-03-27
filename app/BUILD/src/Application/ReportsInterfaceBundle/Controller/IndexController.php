<?php

/**
 * DeskPRO.
 */

namespace Application\ReportsInterfaceBundle\Controller;

use Application\DeskPRO\Entity\ApiToken;

class IndexController extends AbstractController
{
    public function interfaceAction()
    {
        $token               = new ApiToken();
        $token->scope        = ApiToken::SCOPE_SESSION;
        $token->person       = $this->person;
        $token->date_expires = new \DateTime('+1 hour');

        $this->em->persist($token);
        $this->em->flush();

        return $this->render('ReportsInterfaceBundle:Index:interface.html.twig', [
            'api_token'             => $token,
            'session'               => $this->session->getEntity(),
            'initial_request_token' => $this->session->generateSecurityToken('request_token', 600),
        ]);
    }
}
