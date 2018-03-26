<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TempController extends AbstractController
{
    /**
     * "user" is the "old" route name for homepage, and it's important we back port that.
     *
     * @Route("/_delete_perm_cache", name="delete_perm_cache")
     */
    public function clearPortalPermissionsCacheAction(Request $request)
    {
        $this->get('portal_permissions_manager')->invalidatePortalPermissionsCaches();

        return new Response('CALLED: $this->get(\'portal_permissions_manager\')->invalidatePortalPermissionsCaches()');
    }
}
