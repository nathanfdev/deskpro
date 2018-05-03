<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\App\Native\RequestHandler;

interface ApiPackageRequestHandlerInterface
{
    /**
     * @param ApiPackageRequestContext $context
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleApiPackageRequest(ApiPackageRequestContext $context);
}
