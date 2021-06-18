<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\SoapClient;

use DeskPRO\Bundle\AppBundle\Util\HttpClient;

if (!class_exists('\SoapClient', false)) {
    class SafeSoapClient
    {
    }
} else {
    /**
     * Disables libxml_disable_entity_loader so SoapClient will work,
     * but tries detect external entities.
     */
    class SafeSoapClient extends \SoapClient
    {
        public function __construct($wsdl, array $options = null)
        {
            if ($wsdl) {
                $this->_verifyWsdlFile($wsdl);
            }

            $v = libxml_disable_entity_loader(false);

            try {
                parent::__construct($wsdl, $options ?: []);
            } catch (\Exception $e) {
                libxml_disable_entity_loader($v);
                throw $e;
            }

            libxml_disable_entity_loader($v);
        }

        private function _verifyWsdlFile($wsdl)
        {
            $raw = HttpClient::downloadToString($wsdl);
            if (!$raw) {
                throw new SafeSoapClientException('Server', 'dp_bad_response');
            }

            if (preg_match('#<!\s*ENTITY[^>]+SYSTEM\s+[^>]+>#i', $raw)) {
                throw new SafeSoapClientException('Server', 'dp_bad_entity');
            }
        }
    }
}

if (class_exists('\SoapFault')) {
    class SafeSoapClientException extends \SoapFault
    {
    }
}
