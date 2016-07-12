<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace Application\ImportBundle\Reader\ZenDesk\Request;

if (class_exists('\Zendesk\API\CurlRequest')) {
    /**
     * Override CurlRequest to tune curl options.
     *
     * Class CurlRequest
     */
    class CurlRequest extends \Zendesk\API\CurlRequest
    {
        /**
         * @var array
         */
        private $options;

        /**
         * Constructor.
         *
         * @param string $url
         * @param array  $options
         */
        public function __construct($url, array $options = [])
        {
            parent::__construct($url);
            $this->options = $options;
        }

        /**
         * {@inheritdoc}
         */
        public function setopt($option, $value)
        {
            if (isset($this->options[$option])) {
                $value = $this->options[$option];
            }

            return parent::setopt($option, $value);
        }
    }
}
