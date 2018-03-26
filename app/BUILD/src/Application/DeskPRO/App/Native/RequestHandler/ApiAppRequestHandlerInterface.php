<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\App\Native\RequestHandler;

interface ApiAppRequestHandlerInterface
{
    /**
     * @param ApiAppRequestContext $context
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleApiPackageRequest(ApiAppRequestContext $context);
}
