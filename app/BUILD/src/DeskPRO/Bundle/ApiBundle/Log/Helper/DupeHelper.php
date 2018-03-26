<?php

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
        return in_array($mode, $this->getModes());
    }

    public function getModes()
    {
        return $this->resolver->getGlobalSettings()->getSerializedArray('api_log.dupe.modes', []);
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
            'log_dupe'       => self::LOG_DUPE_SAVE,
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
