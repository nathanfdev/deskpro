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

namespace DpBehat\Install;

use Behat\MinkExtension\Context\RawMinkContext;
use DeskPRO\Component\Util\TypeUtils;

class ServerInfoContext extends RawMinkContext
{
    /**
     * @Given I have :code as my server info auth code
     */
    public function iHaveAsMyServerInfoAuth($code)
    {
        global $DP_ENV;
        $DP_ENV->getDatManager()->writeTxtFile('server_info_auth', $code);
    }

    /**
     * @Then the encoded output should decode into :classname
     */
    public function theEncodedOutputShouldDecodeInto($classname)
    {
        $res = $this->getSession()->getPage()->getContent();
        if (!$res) {
            throw new \RuntimeException('Missing content');
        }

        $match = null;
        if (!preg_match('#\-{10,}BEGIN\-{10}(.*?)\-{10,}END\-{10}#s', $res, $match)) {
            throw new \RuntimeException('Invalid content missing begin/end markers.');
        }

        $checker = unserialize(base64_decode(trim($match[1])));
        if (!$checker) {
            throw new \RuntimeException('Failed to decode content');
        }

        $type = TypeUtils::getVarType($checker);

        if ($type !== $classname) {
            throw new \InvalidArgumentException("Expected $classname but got $type");
        }
    }
}
