<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage ApiBundle
 */

namespace Application\ApiBundle\Controller;

use Application\ApiBundle\PermissionStrategy\RequireSessionPermission;

class MySessionController extends AbstractController implements ProtectedControllerInterface
{
    const TOKEN_LIFETIME = 420;

    /**
     * {@inheritDoc}
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

        return $this->createApiResponse(array(
            'request_token' => $this->api_user->session->generateSecurityToken('request_token', self::TOKEN_LIFETIME),
        ));
    }
}
