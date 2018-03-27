<?php

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
