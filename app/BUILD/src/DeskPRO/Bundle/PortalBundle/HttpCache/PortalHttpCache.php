<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\PortalBundle\HttpCache;

use DeskPRO\Component\Util\RegexUtils;
use FOS\HttpCache\SymfonyCache\UserContextSubscriber;
use FOS\HttpCacheBundle\SymfonyCache\EventDispatchingHttpCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class PortalHttpCache extends EventDispatchingHttpCache
{
    const USER_CONTEXT_HASH_HEADER        = 'X-User-Context-Hash';
    const USER_CONTEXT_HASH_ACCEPT_HEADER = 'application/vnd.fos.user-context-hash';

    /**
     * This used to be "guest", but I made it the same as the generated response from a guest with session (below). This is
     * because "Vary" will be different if these are different, and there's no need for that. If we need to distinguish
     * between an "anonymous" and "guest with session" in the app, we can change this value. (NEVER change the GUEST_HASH tho).
     */
    const ANON_NO_SESSION_HASH = 'anon_no_session';

    /**
     * If the guest gets through the anon filter (has a session) the following hash is generated, instead of ANON_NO_SESSION_HASH.
     *
     * NEVER change this value. It is computed with the normal "everyone" usergroup, as a guest would be.
     */
    const GUEST_WITH_SESSION_HASH = '2c297f02c63a1203f83d00f05103617658b9f15f87d578c2d558a7fd2ba6531b';

    /**
     * @var string
     */
    private $basePath;

    /**
     * {@inheritdoc}
     */
    public function __construct(HttpKernelInterface $kernel, $cacheDir, $basePath = '/')
    {
        $this->basePath = $basePath;
        parent::__construct($kernel, $cacheDir);
    }

    protected function getDefaultSubscribers()
    {
        $userContextSubscriber = new UserContextSubscriber(
            [
                'anonymous_hash'          => self::ANON_NO_SESSION_HASH,
                'user_hash_accept_header' => self::USER_CONTEXT_HASH_ACCEPT_HEADER,
                'user_hash_header'        => self::USER_CONTEXT_HASH_HEADER,
                'user_hash_uri'           => $this->basePath.'/_portal_user_hash',
                'user_hash_method'        => 'GET',
                'session_name_prefix'     => 'dpsid',
            ]
        );

        return [$userContextSubscriber];
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        if ($request->attributes->has('internalRequest')) {
            return parent::handle($request, $type, $catch);
        }

        $cacheDisabled = $DP_ENV->getConfig('settings.disable_portal_http_cache');

        // Cache is disabled for agents or users, they need to see content asap
        if (!$cacheDisabled && ($request->cookies->has('dpsid-agent') || $request->cookies->has('dpsid-admin'))) {
            $cacheDisabled = true;
        }

        if ($cacheDisabled) {
            return $this->kernel->handle($request, $type, $catch);
        }

        try {
            $response = parent::handle($request, $type, $catch);

            // response can be false somehow
            if ($response === false) {
                try {
                    return $this->kernel->handle($request, $type, false);
                } catch (\Exception $e) {
                    $r = new Response($response);
                    $r->setMaxAge(0);
                    $r->setSharedMaxAge(0);
                    $r->setPrivate();

                    return $r;
                }
            }
        } catch (\Exception $e) {
            $statusCode = RegexUtils::getMatch(
                '/Error when rendering ".*?" \(Status code is (?P<statusCode>\d+)\)./',
                $e->getMessage(),
                'statusCode'
            );

            $statusCode = $statusCode ? (int) $statusCode : null;
            $response   = null;

            if ($statusCode && $statusCode >= 300 && $statusCode < 500) {
                // error in a tag/esi
                $response = '';
            } elseif ($type !== HttpKernelInterface::MASTER_REQUEST) {
                // error in a tag
                $response = '';
            } else {
                if (strpos($request->getPathInfo(), '/_proxy') === 0) {
                    // error in an independant esi call
                    $response = '';
                }
            }

            if ($response !== null) {
                $r = new Response($response);
                $r->setMaxAge(0);
                $r->setSharedMaxAge(0);
                $r->setPrivate();

                return $r;
            }

            // Otherwise a genuine exception, throw
            throw $e;
        }

        // we don't want this to "look" like it should be cached to the outside world. after this method,
        // we send it to the user. so let's strip away the idea of this response being cachable.
        // this will force clients (and proxies in the middle) to always hit our proxy cache first.

        $response->setMaxAge(0);
        $response->setSharedMaxAge(0);
        $response->setPrivate();

        return $response;
    }

    /**
     * Returns an array of options to customize the Cache configuration.
     *
     * @return array An array of options
     */
    protected function getOptions()
    {
        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        return ['debug' => $DP_ENV->isDebug()];
    }
}
