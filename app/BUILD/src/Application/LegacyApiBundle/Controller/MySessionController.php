<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\LegacyApiBundle\PermissionStrategy\RequireSessionPermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;

/**
 * @ApiModes("all")
 */
class MySessionController extends AbstractController implements ProtectedControllerInterface
{
    const TOKEN_LIFETIME = 420;

    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        return new RequireSessionPermission();
    }

    public function renewRequestTokenAction()
    {
        $session_id = $this->in->getString('session_id');

        if (!$session_id) {
            if ($this->api_user && $this->api_user->session) {
                $session_id = $this->api_user->session->getSessionCode();
            }
        }

        // Ping the session
        if ($session_id) {
            $session = $this->em->getRepository('DeskPRO:Session')->getSessionFromCode($session_id);
            if ($session) {
                $session->date_last      = new \DateTime();
                $session->date_last_page = new \DateTime();
                $this->em->persist($session);
                $this->em->flush();
            }
        }

        return $this->createApiResponse([
            'request_token' => $this->api_user->session->generateSecurityToken('request_token', self::TOKEN_LIFETIME),
            'session_id'    => $session_id,
        ]);
    }
}
