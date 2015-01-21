<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\DeskPRO\SoapClient;

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
                parent::__construct($wsdl, $options ?: array());
            } catch (\Exception $e) {
                libxml_disable_entity_loader($v);
                throw $e;
            }

            libxml_disable_entity_loader($v);
        }

        private function _verifyWsdlFile($wsdl)
        {
            $raw = @file_get_contents($wsdl, null, stream_context_create(array('http' => array('timeout'  => 10))));
            if (!$raw) {
                throw new SafeSoapClientException("Server", "dp_bad_response");
            }

            if (preg_match('#<!\s*ENTITY[^>]+SYSTEM\s+[^>]+>#i', $raw)) {
                throw new SafeSoapClientException("Server", "dp_bad_entity");
            }
        }
    }
}

class SafeSoapClientException extends \SoapFault
{

}