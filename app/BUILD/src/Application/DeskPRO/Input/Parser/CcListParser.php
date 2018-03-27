<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Input\Parser;

use Application\DeskPRO\People\EmailAddressValidator;

class CcListParser
{
    /**
     * @var \Application\DeskPRO\People\EmailAddressValidator
     */
    private $validator;

    /**
     * @param EmailAddressValidator $validator
     */
    public function __construct(EmailAddressValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param string $input
     *
     * @return string[]
     */
    public function parse($input)
    {
        $emails = [];

        $matches    = null;
        $char_group = preg_quote(' ,;<>|', '#');
        if (!preg_match_all("#(?<=[$char_group])([^$char_group]+@[^$char_group]+)(?=[$char_group])#", "|$input|", $matches)) {
            return [];
        }

        foreach ($matches[1] as $seg) {
            $seg = trim($seg);
            if (!$seg) {
                continue;
            }

            if ($this->validator->isValidUserEmail($seg)) {
                $emails[] = $seg;
            }
        }

        return $emails;
    }
}
