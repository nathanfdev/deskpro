<?php

/**
 * DeskPRO.
 *
 * @category Translate
 */

namespace Application\DeskPRO\Translate;

use Orb\Util\Strings;

/**
 * This takes a Twig template and covnerts phrase tags into literal phrases.
 */
class DephrasifyTemplate
{
    /** @var \Application\DeskPRO\Translate\Translate */
    protected $translate;

    public function __construct(Translate $translate)
    {
        $this->translate = $translate;
    }

    /**
     * @param string $string The raw twig template
     *
     * @return string
     */
    public function expand($string)
    {
        $string = $this->expandSimplePhrases($string);
        $string = $this->expandVariablePhrases($string);

        return $string;
    }

    /**
     * Expands sub-phrases that are sometimes found in other phrases:.
     *
     * "The quick brown {{phrase.fox}} jumped over the lazy dog"
     *
     * These are run on phrases themselves (so not templates that use phrases)
     *
     * @see Translate::phrase()
     *
     * @param  $string
     *
     * @return mixed
     */
    public function expandSubphrases($string)
    {
        $matches = null;
        if (!preg_match_all('#\{\{\s*phrase\.([a-zA-Z0-9\.\-_]+)\s\}\}#', $string, $matches, PREG_SET_ORDER)) {
            return $string;
        }

        foreach ($matches as $match) {
            $phrase = $match[1];

            // These are simple phrases by defintion, they cant
            // use variables. So they're simple replacements

            $phrase_text = $this->translate->phrase($phrase);
            $string      = str_replace($match[0], $phrase_text, $string);
        }

        return $string;
    }

    /**
     * Expands simple phrases.
     *
     * @param string $string
     *
     * @return string
     */
    public function expandSimplePhrases($string)
    {
        $matches = null;
        if (!preg_match_all('#\{\{\s*phrase\((\'|\")([a-zA-Z0-9\.\-_]+)(\'|\")\)\s*\}\}#', $string, $matches, PREG_SET_ORDER)) {
            return $string;
        }

        foreach ($matches as $match) {
            $phrase      = $match[2];
            $phrase_text = $this->translate->phrase($phrase);
            $phrase_text = $this->expandSubphrases($phrase_text);

            $string = str_replace($match[0], $phrase_text, $string);
        }

        return $string;
    }

    /**
     * Tries to expand phrases with variables:.
     *
     * {{ phrase('
     *
     * @param string $string
     *
     * @return string
     */
    public function expandVariablePhrases($string)
    {
        $matches = null;
        if (!preg_match_all('#\{\{\s*phrase\((\'|\")([a-zA-Z0-9\.\-_]+)(\'|\")\s*,\s*\{(.*?)\}\s*\)\s*\}\}#', $string, $matches, PREG_SET_ORDER)) {
            return $string;
        }

        foreach ($matches as $match) {
            $line        = $match[0];
            $phrase      = $match[2];
            $hash_string = '{ '.$match[4].' }';
            $phrase_text = $this->translate->phrase($phrase);
            $phrase_text = $this->expandSubphrases($phrase_text);

            $var_places = null;
            preg_match_all('#\{\{\s*([a-zA-Z0-9_]+)\s*\}\}#', $phrase_text, $var_places, PREG_SET_ORDER);

            // Empty phrase (ie doesnt exist) or phrase isnt using any vars
            // we can just continue out now
            if (!$phrase_text or !$var_places) {
                $string = str_replace($match[0], $phrase_text, $string);
                continue;
            }

            $found_all    = true;
            $find_replace = [];

            foreach ($var_places as $var) {
                $varname  = $var[1];
                $val_expr = $this->_findVarInHashString($varname, $hash_string);
                if ($val_expr === false) {
                    $found_all = false;
                    break;
                }

                $find_replace[$var[0]] = $val_expr;
            }

            if (!$found_all) {
                continue;
            }

            foreach ($find_replace as $k => $v) {
                $phrase_text = str_replace($k, $v, $phrase_text);
            }

            $string = str_replace($line, $phrase_text, $string);
        }

        return $string;
    }

    /**
     * Tries to parse out the value of a key in a hash string.
     *
     * @param  $varname
     * @param  $hash_string
     *
     * @return null|string
     */
    protected function _findVarInHashString($varname, $hash_string)
    {
        $m         = null;
        $varname_q = preg_quote($varname, '#');

        $key_string = Strings::extractRegexMatch("#(\'|\")$varname_q(\'|\")\s*:\s*#", $hash_string, 0);

        // Not found
        if (!$key_string) {
            return;
        }

        $value_expr = null;
        if (!preg_match("#(\'|\")$varname_q(\'|\")\s*:\s*(((\'|\")(?P<quoted>.*?)(\'|\"))|((?P<expr>.*?)(\s|,|\})))#", $hash_string, $value_expr)) {
            return;
        }

        if (isset($value_expr['quoted'])) {
            return $value_expr['quoted'];
        } else {
            return '{{ '.$value_expr['expr'].' }}';
        }
    }
}
