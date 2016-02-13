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

namespace DeskPRO\Bundle\ApiBundle\Log\Helper;

use DeskPRO\Component\Util\RandUtils;
use Symfony\Component\HttpFoundation\HeaderBag;

/**
 * Class LogHelper.
 */
class LogHelper extends AbstractLogHelper
{
    /** @var string */
    protected $request_id;

    /** @var bool */
    protected $client_generated_request_id = false;

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
     * @return bool
     */
    public function isLoggingEnabled()
    {
        return $this->resolver->getGlobalSettings()->get('api_log.enabled');
    }

    /**
     * @return bool
     */
    public function isClientRequestedLog()
    {
        return $this->client_generated_request_id;
    }
}
