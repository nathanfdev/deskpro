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
namespace DeskPRO\Bundle\PortalBundle\HttpCache;

use FOS\HttpCache\SymfonyCache\UserContextSubscriber;
use FOS\HttpCacheBundle\SymfonyCache\EventDispatchingHttpCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class PortalHttpCache extends EventDispatchingHttpCache
{
    const USER_CONTEXT_HASH_HEADER        = 'X-User-Context-Hash';
    const USER_CONTEXT_HASH_ACCEPT_HEADER = 'application/vnd.fos.user-context-hash';

    /**
     * This used to be "guest", but I made it the same as the generated response from a guest with session (below). This is
     * because "Vary" will be different if these are different, and there's no need for that. If we need to distingush
     * between an "anonymous" and "guest with session" in the app, we can change this value. (NEVER change the GUEST_HASH tho).
     */
    const ANON_HASH = '2c297f02c63a1203f83d00f05103617658b9f15f87d578c2d558a7fd2ba6531b';

    /**
     * If the guest gets through the anon filter (has a session) the following hash is always used, instead of ANON_HASH.
     *
     * This is generated in PortalUserHashContextProvider, and then hashed by FOSHttpCacheBundle's service.
     * We can alter the generator if we need to, but just use "portal_cache_helper" service to determine if its a guest request.
     *
     * NEVER change this value.
     */
    const GUEST_HASH = '2c297f02c63a1203f83d00f05103617658b9f15f87d578c2d558a7fd2ba6531b';

    protected function getDefaultSubscribers()
    {
        $user_context_subscriber = new UserContextSubscriber(
            array(
                'anonymous_hash'          => self::ANON_HASH,
                'user_hash_accept_header' => self::USER_CONTEXT_HASH_ACCEPT_HEADER,
                'user_hash_header'        => self::USER_CONTEXT_HASH_HEADER,
                'user_hash_uri'           => '/_portal_user_hash',
                'user_hash_method'        => 'GET',
                'session_name_prefix'     => 'PHPSESSID',
            )
        );

        return array($user_context_subscriber);
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        global $DP_CONFIG;

        if (isset($DP_CONFIG['portal_disable_cache']) && $DP_CONFIG['portal_disable_cache']) {
            return $this->kernel->handle($request, $type, $catch);
        }

        return parent::handle($request, $type, $catch);
    }

    /**
     * Returns an array of options to customize the Cache configuration.
     *
     * @return array An array of options
     */
    protected function getOptions()
    {
        return array('private_headers' => array(), 'debug' => false);
    }
}
