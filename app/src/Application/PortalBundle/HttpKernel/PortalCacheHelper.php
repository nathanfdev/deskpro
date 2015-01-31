<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
 * @subpackage
 */

namespace Application\PortalBundle\HttpKernel;


use Application\DeskPRO\PortalBundle\HttpKernel\PortalHttpCache;
use Symfony\Component\HttpFoundation\RequestStack;

class PortalCacheHelper
{
    /**
     * @var RequestStack
     */
    private $request_stack;

    /**
     * @var null|bool used to only make guest decision once per master request
     */
    private $is_guest;

    public function __construct(RequestStack $request_stack)
    {
        $this->request_stack = $request_stack;
        $this->is_guest = null;
    }

    /**
     * Is the master request a guest request?
     *
     * @return bool true if in a guest request
     */
    public function isGuestRequest()
    {
        // only make the decision once per php run
        if (null !== $this->is_guest) {
            return $this->is_guest;
        }

        return $this->is_guest = $this->determineIfGuestRequest();
    }

    /**
     * @return null|string
     */
    public function getUserContextHash()
    {
        $current_request = $this->request_stack->getMasterRequest();

        if (!$current_request->headers->has(PortalHttpCache::USER_CONTEXT_HASH_HEADER)) {
            return null;
        }

        return $current_request->headers->get(PortalHttpCache::USER_CONTEXT_HASH_HEADER);
    }

    /**
     * Is the master request a guest request, or not?
     *
     * @return bool true if guest
     */
    private function determineIfGuestRequest()
    {
        if (!$user_hash = $this->getUserContextHash()) {
            return true; // if no user context hash header: default to a guest!
        }

        return in_array($user_hash, array(PortalHttpCache::ANON_HASH, PortalHttpCache::GUEST_HASH));
    }

}
