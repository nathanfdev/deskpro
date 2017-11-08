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

namespace DeskPRO\Bundle\AppBundle\Cache\Resolver;

use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\AppEnv\AppEnvInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpCache\Store;

/**
 * Class FileResolver.
 */
class FileResolver implements ResolverInterface
{
    /**
     * @var Store
     */
    protected $store;

    /**
     * @var Response
     */
    protected $resolved_response;

    /**
     * @var int
     */
    protected $resolve_status = 0;

    /**
     * @var SettingsResolver
     */
    protected $settingsResolver;

    /**
     * Constructor.
     *
     * @param AppEnvInterface  $appEnv
     * @param SettingsResolver $settingsResolver
     */
    public function __construct(AppEnvInterface $appEnv, SettingsResolver $settingsResolver)
    {
        $this->store            = new Store($appEnv->getUserCacheDir().'/http_cache/'.$appEnv->getAppName().'/api');
        $this->settingsResolver = $settingsResolver;
    }

    /**
     * @param Request $request
     *
     * @return null|Response
     */
    public function resolve(Request $request)
    {
        if ($this->isEnabled() && !$this->resolved_response && $this->resolve_status === 0) {
            $this->resolved_response = $this->store->lookup($request);
            $this->resolve_status    = 1; // means lookup was performed
        }

        return $this->resolved_response;
    }

    /**
     * @todo check if needed
     *
     * @return bool
     */
    public function hasResolvedResponse()
    {
        return (bool) $this->resolve_status && $this->resolved_response;
    }

    /**
     * {@inheritdoc}
     */
    public function write(Request $request, Response $response)
    {
        $this->store->write($request, $response);
    }

    /**
     * This is simplified HttpCache method. We are not working with ESI, so we don't have anything like X-Body-Eval.
     * So just read the X-Body-File content and paste into response content.
     *
     * @param Response $response
     *
     * @return Response $response
     */
    public function restoreResponseBody(Response $response)
    {
        if (!$this->resolved_response) {
            throw new \LogicException('There is no response to restore body');
        }

        if ($response->headers->has('X-Body-File')) {
            $response->setContent(file_get_contents($response->headers->get('X-Body-File')));
        } else {
            return $response;
        }

        $response->headers->remove('X-Body-File');

        return $response;
    }

    /**
     * @return bool
     */
    protected function isEnabled()
    {
        return $this->settingsResolver->getGlobalSettings()->get('response.cache.enabled', false);
    }
}
