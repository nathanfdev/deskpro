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

namespace DeskPRO\Bundle\ApiBundle\Log;

use Application\DeskPRO\NewSettings\SettingsResolver;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class LogHelper.
 */
class LogHelper
{
    /**
     * @const string
     */
    const REQUEST_ID_HEADER = 'X-DeskPRO-Request-ID';

    /**
     * @const string
     */
    const REQUEST_ID_CLIENT_HEADER = 'X-DeskPRO-Request-ID';

    /**
     * @var SettingsResolver
     */
    protected $resolver;

    /**
     * @param SettingsResolver $resolver
     */
    public function __construct(SettingsResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * And where is my method overloading? :(.
     *
     * @param Response $response
     *
     * @return null|string
     */
    public function getRequestIdFromResponse(Response $response)
    {
        return $this->getRequestId($response->headers);
    }

    /**
     * @param array|HeaderBag $headers
     *
     * @return string|null
     */
    public function getRequestId($headers)
    {
        switch (true) {
            case $headers instanceof HeaderBag:
                break;
            case is_array($headers):
                $headers = new HeaderBag($headers);
                break;
            default:
                throw new \InvalidArgumentException(
                    sprintf(
                        'Headers must be an array or instance of %s, %s given',
                        HeaderBag::class,
                        is_object($headers) ? get_class($headers) : gettype($headers)
                    ));
        }

        return $headers->get(self::REQUEST_ID_HEADER);
    }

    /**
     * @return bool
     */
    public function isLoggingEnabled()
    {
        return $this->resolver->getGlobalSettings()->get('api_log.enabled');
    }
}
