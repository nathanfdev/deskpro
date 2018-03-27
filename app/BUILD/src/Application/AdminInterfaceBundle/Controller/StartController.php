<?php

/**
 * DeskPRO.
 */

namespace Application\AdminInterfaceBundle\Controller;

use Application\DeskPRO\Entity\ApiToken;
use Symfony\Component\HttpFoundation\Request;

class StartController extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    public function preActionHandler(Request $request, $action, $arguments = null)
    {
        if (defined('DPC_IS_CLOUD')) {
            throw $this->createNotFoundException();
        }

        return parent::preActionHandler($request, $action, $arguments);
    }

    //###################################################################################################################
    // index
    //###################################################################################################################

    public function indexAction()
    {
        $token               = new ApiToken();
        $token->scope        = ApiToken::SCOPE_SESSION;
        $token->person       = $this->person;
        $token->date_expires = new \DateTime('+1 hour');

        $this->em->persist($token);
        $this->em->flush();

        $not_user = $this->person->getLabelManager()->hasLabel('not_user');

        return $this->render('AdminInterfaceBundle:Start:layout.html.twig', [
            'api_token'             => $token,
            'session'               => $this->session->getEntity(),
            'initial_request_token' => $this->session->generateSecurityToken('request_token', 600),
            'not_user'              => $not_user,
        ]);
    }
}
