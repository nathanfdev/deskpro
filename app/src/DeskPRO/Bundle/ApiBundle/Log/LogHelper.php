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
use DeskPRO\Bundle\ApiBundle\Log\Finder\FinderInterface;
use DeskPRO\Component\Util\RandUtils;
use Symfony\Component\HttpFoundation\HeaderBag;

/**
 * Class LogHelper.
 */
class LogHelper
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

    const EAGER_ON  = 1;
    const EAGER_OFF = 0;

    /**
     * @var SettingsResolver
     */
    protected $resolver;

    /**
     * @var FinderInterface
     */
    protected $finder;

    /** @var string */
    protected $request_id;

    /** @var array */
    protected $request_options;

    /** @var bool */
    protected $client_generated_request_id = false;

    /**
     * @var bool
     */
    protected $request_is_processed = false;

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
     * @param array|HeaderBag $headers
     *
     * @return string|null
     */
    public function getRequestId($headers)
    {
        if (!$this->request_id) {
            $headers          = $this->mutateHeaders($headers);
            $this->request_id =
                ($headers->has(self::REQUEST_ID_CLIENT_HEADER))
                    ? $this->generateRequestId(true, $headers->get(self::REQUEST_ID_CLIENT_HEADER))
                    : $this->generateRequestId();
        }

        return $this->request_id;
    }

    /**
     * @param bool|false $incoming
     * @param string     $incoming_value
     *
     * @return string
     */
    protected function generateRequestId($incoming = false, $incoming_value = '')
    {
        if ($incoming && !$incoming_value) {
            throw new \InvalidArgumentException(
                sprintf(
                    'In case you have incoming %s header, you should provide it\'s value when generating ID',
                    self::REQUEST_ID_CLIENT_HEADER
                ));
        }
        if ($incoming) {
            $value                             = $incoming_value;
            $suffix                            = self::CLIENT_SUFFIX;
            $prefix                            = '';
            $this->client_generated_request_id = true;
        } else {
            $value  = RandUtils::randomStringFormat('%30cn');
            $suffix = self::DESKPRO_SUFFIX;
            $prefix = time().'-';
        }

        return sprintf('%s%s-%s', $prefix, $value, $suffix);
    }

    /**
     * @param $headers
     *
     * @return array
     */
    public function getRequestOptions($headers)
    {
        if (!$this->request_options) {
            $headers = $this->mutateHeaders($headers);
            if ($headers->has(self::REQUEST_CLIENT_OPTIONS_HEADER)) {
                $this->request_options = $this->mergeOptions(json_decode($headers->get(self::REQUEST_CLIENT_OPTIONS_HEADER), true));
            } else {
                $this->request_options = $this->getDefaultOptions();
            }
        }

        return $this->request_options;
    }

    /**
     * @return array
     */
    protected function getDefaultOptions()
    {
        return [
            'duplicate_mode' => self::DUPLICATE_MODE_FAIL,
            'failure_mode'   => self::FAILURE_MODE_SKIP,
            'eager'          => self::EAGER_OFF,
        ];
    }

    /**
     * @param $options
     *
     * @return array
     */
    protected function mergeOptions($options)
    {
        $options = array_merge($this->getDefaultOptions(), $options);

        return array_intersect_key($options, $this->getDefaultOptions());
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
     * @return bool
     */
    public function isLoggingEnabled()
    {
        return $this->resolver->getGlobalSettings()->get('api_log.enabled');
    }

    /**
     * @return bool
     */
    public function isClientRequestdLog()
    {
        return $this->client_generated_request_id;
    }

    /**
     * @return bool
     */
    public function shouldLog()
    {
        return ($this->isLoggingEnabled() || $this->client_generated_request_id) && !$this->request_is_processed;
    }

    /**
     * @param $request_id
     *
     * @return mixed
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
