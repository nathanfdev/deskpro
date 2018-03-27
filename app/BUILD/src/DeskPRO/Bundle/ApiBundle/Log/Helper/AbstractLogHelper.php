<?php

namespace DeskPRO\Bundle\ApiBundle\Log\Helper;

use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\ApiBundle\Log\Finder\FinderInterface;
use DeskPRO\Bundle\AppBundle\Entity\ApiLog;
use Symfony\Component\HttpFoundation\HeaderBag;

abstract class AbstractLogHelper
{
    const REQUEST_ID_HEADER             = 'X-DeskPRO-Request-ID';
    const REQUEST_ID_CLIENT_HEADER      = 'X-DeskPRO-Client-Request-ID';
    const REQUEST_CLIENT_OPTIONS_HEADER = 'X-DeskPRO-Client-Request-Options';

    const CLIENT_SUFFIX  = 'c';
    const DESKPRO_SUFFIX = 'd';

    const DUPLICATE_MODE_FAIL   = 'fail';
    const DUPLICATE_MODE_RESEND = 'resend';

    const FAILURE_MODE_SAVE = 'save';
    const FAILURE_MODE_SKIP = 'skip';

    const LOG_DUPE_SAVE = 'save';

    const EAGER_ON  = 1;
    const EAGER_OFF = 0;

    /**
     * @var SettingsResolver
     */
    protected $resolver;

    /** @var array */
    protected $request_options;

    /**
     * @var FinderInterface
     */
    protected $finder;

    /**
     * @param SettingsResolver $resolver
     * @param FinderInterface  $finder
     */
    public function __construct(SettingsResolver $resolver, FinderInterface $finder)
    {
        $this->resolver = $resolver;
        $this->finder   = $finder;
    }

    /**
     * @param $headers
     *
     * @return HeaderBag
     */
    protected function mutateHeaders($headers)
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

        return $headers;
    }

    /**
     * @param $request_id
     *
     * @return ApiLog
     */
    public function findRequest($request_id)
    {
        return $this->finder->find($request_id);
    }

    /**
     * @param bool $request_is_processed
     *
     * @return $this
     */
    public function setRequestIsProcessed($request_is_processed)
    {
        $this->request_is_processed = $request_is_processed;

        return $this;
    }
}
