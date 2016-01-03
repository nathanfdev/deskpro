<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Security\Firewall;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Session;
use DeskPRO\Bundle\AppBundle\Security\DpTransferSessionAuthToken;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;

class DpTransferSessionAuthListener extends AbstractAuthenticationListener implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    protected function requiresAuthentication(Request $request)
    {
        if ($request->attributes->get('_route') == 'portal_agent_login') {
            // impossible to run if user is trying to impersonate
            return false;
        }

        // if there is no session, we can't be impersonating
        if (!$request->hasPreviousSession()) {
            return false;
        }

        if ($request->getSession()->get('is_impersonating', false)) {
            return false;
        }

        if ($session_id = $this->checkAgentInterfaceAuthNeedsTransfer($request)) {
            return true;
        }

        return false;
    }

    /**
     * Returns null if nothing is interesting, but will give you a session ID from the "other side"
     * if one exists and we are not logged into the portal yet.
     *
     * @param Request $request
     *
     * @return mixed the session ID from the agent/admin/reporting side or FALSE.
     */
    protected function checkAgentInterfaceAuthNeedsTransfer(Request $request)
    {
        // not logged in to portal
        if ($sid = $request->cookies->get('dpsid-agent')) {
            $session_id    = Session::getIdFromCode($sid);
            $agent_session = App::getDb()->fetchAssoc(
                '
                    SELECT person_id, auth
                    FROM sessions
                    WHERE id = ?
                ',
                array(
                    $session_id,
                )
            );

            list(, $auth) = explode('-', $sid);
            if ($agent_session && $agent_session['auth'] == $auth && $agent_session['person_id']) {
                if ($person = App::getEntityRepository('DeskPRO:Person')->find($agent_session['person_id'])) {
                    if (
                        // if the session isnt started, or if it is and we dont have a portal logged in user
                        // NOTE: v. important to be careful to not start the session here
                        !$request->getSession()->isStarted()
                        || ($request->getSession()->isStarted() && !$request->getSession()->get('auth_person_id'))
                    ) {
                        return $sid;
                    }
                }
            }
        }

        return false;
    }

    protected function attemptAuthentication(Request $request)
    {
        $tokenOrResponse = null;

        if ($session_id = $this->checkAgentInterfaceAuthNeedsTransfer($request)) {
            // transfer a login from agent/admin/reporting to portal
            $tokenOrResponse = new DpTransferSessionAuthToken(null, $session_id);
        }

        if ($tokenOrResponse instanceof Response) {
            return $tokenOrResponse;
        }

        $r = $this->authenticationManager->authenticate($tokenOrResponse);

        return $r;
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
