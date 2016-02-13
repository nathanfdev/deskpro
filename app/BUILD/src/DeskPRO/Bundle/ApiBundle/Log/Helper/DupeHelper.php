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

class DupeHelper extends AbstractLogHelper
{
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

    public function suitableMode($mode)
    {
        return in_array($mode, $this->resolver->getGlobalSettings()->get('api_log.dupe.modes'));
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
}
