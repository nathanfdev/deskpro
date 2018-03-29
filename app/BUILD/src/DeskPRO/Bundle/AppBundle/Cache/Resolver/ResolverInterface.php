<?php

namespace DeskPRO\Bundle\AppBundle\Cache\Resolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Interface ResolverInterface.
 */
interface ResolverInterface
{
    /**
     * @param Request $request
     *
     * @return null|Response
     */
    public function resolve(Request $request);

    /**
     * @param Request  $request
     * @param Response $response
     */
    public function write(Request $request, Response $response);
}
