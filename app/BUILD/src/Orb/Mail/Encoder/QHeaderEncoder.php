<?php

namespace Orb\Mail\Encoder;

/**
 * @see https://github.com/PHPMailer/PHPMailer/blob/master/src/PHPMailer.php#L3611
 */
class QHeaderEncoder implements \Swift_Mime_HeaderEncoder
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'Q';
    }

    /**
     * {@inheritDoc}
     */
    public function encodeString($string, $firstLineOffset = 0, $maxLineLength = 0)
    {
        //There should not be any EOL in the string
        $pattern = '';
        $encoded = str_replace(["\r", "\n"], '', $string);

        //RFC 2047 section 5.1
        //Replace every high ascii, control, =, ? and _ characters
        $pattern = '\000-\011\013\014\016-\037\075\077\137\177-\377' . $pattern;

        $matches = [];
        if (preg_match_all("/[{$pattern}]/", $encoded, $matches)) {
            //If the string contains an '=', make sure it's the first thing we replace
            //so as to avoid double-encoding
            $eqkey = array_search('=', $matches[0], true);
            if (false !== $eqkey) {
                unset($matches[0][$eqkey]);
                array_unshift($matches[0], '=');
            }
            foreach (array_unique($matches[0]) as $char) {
                $encoded = str_replace($char, '%' . sprintf('%02X', ord($char)), $encoded);
            }
        }
        //Replace spaces with _ (more readable than =20)
        //RFC 2047 section 4.2(2)
        return str_replace(' ', '_', $encoded);
    }

    /**
     * {@inheritDoc}
     */
    public function charsetChanged($charset)
    {
    }
}
