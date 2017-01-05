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
