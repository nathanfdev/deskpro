<?php

/**
 * Orb.
 *
 * @category Input
 */

namespace Orb\Input\Cleaner\CleanerPlugin;

use Orb\Input\Cleaner\Cleaner;
use Orb\Util\Strings;

/**
 * This re-defines the basic string types in Basic.php to filter out xss
 * from all input.
 */
class BasicXss implements CleanerPlugin
{
    private $xss_clean;

    public function getXssCleaner()
    {
        if ($this->xss_clean) {
            return $this->xss_clean;
        }

        $this->xss_clean = new __DP_CI_Security();

        return $this->xss_clean;
    }

    public function getCleanerId()
    {
        return 'basic_xss';
    }

    public function getCleanerTypes()
    {
        // These override the basic ones
        return [
            'str',
            'string',
            'str_notrim',
        ];
    }

    public function cleanValue($value, $type, array $options, Cleaner $cleaner)
    {
        $value = $cleaner->getCleaner('basic')->cleanValue($value, $type, $options, $cleaner);

        if (isset($options['noclean']) && $options['noclean']) {
            return $value;
        }

        if (!$value) {
            return $value;
        }

        if (ctype_alnum($value) || !preg_match('#(\'|"|&|<|>)#', $value)) {
            return $value;
        }

        return $this->getXssCleaner()->xss_clean($value);
    }
}

/**
 * This is a modified version of the XSS cleaner found in CodeIngiter.
 *
 * Changes:
 * - Got rid of CI specific function calls
 * - Replaced a few [removed] replacement texts to make replacements less obvious for usually inocuous cases
 *
 * @link http://codeigniter.com/
 */
class __DP_CI_Security
{
    protected $_xss_hash = '';

    protected $_never_allowed_str = [
        'document.cookie' => 'document,cookie',
        'document.write'  => 'document,write',
        '.parentNode'     => ',parentNode',
        '.innerHTML'      => ',innerHTML',
        '-moz-binding'    => '',
        '<!--'            => '&lt;!--',
        '-->'             => '--&gt;',
        '<![CDATA['       => '&lt;![CDATA[',
        '<comment>'       => '&lt;comment&gt;',
    ];

    protected $_never_allowed_regex = [
        'javascript\s*:',
        '(document|(document\.)?window)\.(location|on\w*)',
        'expression\s*(\(|&\#40;)', // CSS and IE
        'vbscript\s*:', // IE, surprise!
        'wscript\s*:', // IE
        'jscript\s*:', // IE
        'vbs\s*:', // IE
        'Redirect\s+30\d:',
        "([\"'])?data\s*:[^\\1]*?base64[^\\1]*?,[^\\1]*?\\1?",
    ];

    public function xss_clean($str, $is_image = false)
    {
        if (is_array($str)) {
            while (list($key) = each($str)) {
                $str[$key] = $this->xss_clean($str[$key]);
            }

            return $str;
        }

        $str = Strings::removeInvisibleCharacters($str);

        do {
            $str = rawurldecode($str);
        } while (preg_match('/%[0-9a-f]{2,}/i', $str));

        $str = preg_replace_callback("/[^a-z0-9>]+[a-z0-9]+=([\'\"]).*?\\1/si", [$this, '_convert_attribute'], $str);
        $str = preg_replace_callback('/<\w+.*/si', [$this, '_decode_entity'], $str);

        $str = Strings::removeInvisibleCharacters($str);

        $str = str_replace("\t", ' ', $str);

        $converted_string = $str;

        $str = $this->_do_never_allowed($str);

        if ($is_image === true) {
            $str = preg_replace('/<\?(php)/i', '&lt;?\\1', $str);
        } else {
            $str = str_replace(['<?', '?'.'>'], ['&lt;?', '?&gt;'], $str);
        }

        $words = [
            'javascript', 'expression', 'vbscript', 'jscript', 'wscript',
            'vbs', 'script', 'base64', 'applet', 'alert', 'document',
            'write', 'cookie', 'window', 'confirm', 'prompt',
        ];

        foreach ($words as $word) {
            $word = implode('\s*', str_split($word)).'\s*';
            $str  = preg_replace_callback('#('.substr($word, 0, -3).')(\W)#is', [$this, '_compact_exploded_words'], $str);
        }

        do {
            $original = $str;

            if (preg_match('/<a/i', $str)) {
                $str = preg_replace_callback('#<a[^a-z0-9>]+([^>]*?)(?:>|$)#si', [$this, '_js_link_removal'], $str);
            }

            if (preg_match('/<img/i', $str)) {
                $str = preg_replace_callback('#<img[^a-z0-9]+([^>]*?)(?:\s?/?>|$)#si', [$this, '_js_img_removal'], $str);
            }

            if (preg_match('/script|xss/i', $str)) {
                $str = preg_replace('#</*(?:script|xss).*?>#si', '[script]', $str);
            }
        } while ($original !== $str);

        unset($original);

        $str = $this->_remove_evil_attributes($str, $is_image);

        $naughty = 'alert|prompt|confirm|applet|audio|basefont|base|behavior|bgsound|blink|body|embed|expression|form|frameset|frame|head|html|ilayer|iframe|input|button|select|isindex|layer|link|meta|keygen|object|plaintext|style|script|textarea|title|math|video|svg|xml|xss';
        $str     = preg_replace("#<(/|)($naughty)([^>]*)>#is", '[$1$2$3]', $str);

        $str = preg_replace(
            '#(alert|prompt|confirm|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si',
            '\\1\\2&#40;\\3&#41;',
            $str
        );

        $str = $this->_do_never_allowed($str);

        if ($is_image === true) {
            return $str === $converted_string;
        }

        return $str;
    }

    public function xss_hash()
    {
        if ($this->_xss_hash == '') {
            mt_srand();
            $this->_xss_hash = md5(time() + mt_rand(0, 1999999999));
        }

        return $this->_xss_hash;
    }

    public function entity_decode($str)
    {
        if (strpos($str, '&') === false) {
            return $str;
        }

        static $_entities;

        $charset = 'UTF-8';

        $flag = version_compare(PHP_VERSION, '5.4', '>=') ? ENT_COMPAT | ENT_HTML5 : ENT_COMPAT;

        do {
            $str_compare = $str;

            if ($c = preg_match_all('/&[a-z]{2,}(?![a-z;])/i', $str, $matches)) {
                if (!isset($_entities)) {
                    $_entities = array_map(
                        'strtolower',
                        version_compare(PHP_VERSION, '5.3.4', '>=')
                            ? get_html_translation_table(HTML_ENTITIES, $flag, $charset)
                            : get_html_translation_table(HTML_ENTITIES, $flag)
                    );

                    if ($flag === ENT_COMPAT) {
                        $_entities[':']  = '&colon;';
                        $_entities['(']  = '&lpar;';
                        $_entities[')']  = '&rpar';
                        $_entities["\n"] = '&newline;';
                        $_entities["\t"] = '&tab;';
                    }
                }

                $replace = [];
                $matches = array_values(array_unique(array_map('strtolower', $matches[0])));
                $c       = count($matches);
                for ($i = 0; $i < $c; ++$i) {
                    if (($char = array_search($matches[$i].';', $_entities, true)) !== false) {
                        $replace[$matches[$i]] = $char;
                    }
                }

                $str = str_ireplace(array_keys($replace), array_values($replace), $str);
            }

            // Decode numeric & UTF16 two byte entities
            $str = html_entity_decode(
                preg_replace('/(&#(?:x0*[0-9a-f]{2,5}(?![0-9a-f;])|(?:0*\d{2,4}(?![0-9;]))))/iS', '$1;', $str),
                $flag,
                $charset
            );
        } while ($str_compare !== $str);

        return $str;
    }

    protected function _compact_exploded_words($matches)
    {
        return preg_replace('/\s+/s', '', $matches[1]).$matches[2];
    }

    protected function _remove_evil_attributes($str, $is_image)
    {
        // All javascript event handlers (e.g. onload, onclick, onmouseover), style, and xmlns
        $evil_attributes = ['on\w*', 'style', 'xmlns', 'formaction', 'form', 'xlink:href'];

        if ($is_image === true) {
            /*
             * Adobe Photoshop puts XML metadata into JFIF images,
             * including namespacing, so we have to allow this for images.
             */
            unset($evil_attributes[array_search('xmlns', $evil_attributes)]);
        }

        $str = preg_replace_callback('#<([^>]*)>#', function ($m) use ($evil_attributes) {
            $str = $m[1];
            do {
                $count = 0;
                $attribs = [];

                // find occurrences of illegal attribute strings with quotes (042 and 047 are octal quotes)
                preg_match_all('/(?<!\w)('.implode('|', $evil_attributes).')\s*=\s*(\042|\047)([^\\2]*?)(\\2)/is', $str, $matches, PREG_SET_ORDER);

                foreach ($matches as $attr) {
                    $attribs[] = $attr[0];
                }

                // find occurrences of illegal attribute strings without quotes
                preg_match_all('/(?<!\w)('.implode('|', $evil_attributes).')\s*=\s*([^\s>]*)/is', $str, $matches, PREG_SET_ORDER);

                foreach ($matches as $attr) {
                    $attribs[] = $attr[0];
                }

                // replace illegal attribute strings that are inside an html tag
                if (count($attribs) > 0) {
                    foreach ($attribs as $a) {
                        $str = str_replace($a, '', $str, $count);
                    }
                }
            } while ($count);

            return "<$str>";
        }, $str, -1, $count);

        // remaining evil attr OUTSIDE of tags we should just mangle
        // eg onerror= becomes "on error="
        $str = preg_replace_callback('/\s+('.implode('|', $evil_attributes).')(\s*=\s*(\042|\047|\w))/is', function ($m) {
            $harmless = $m[1];
            $harmless = substr($harmless, 0, 2).' '.substr($harmless, 2);

            return ' '.$harmless.$m[2];
        }, $str);

        return $str;
    }

    protected function _js_link_removal($match)
    {
        return str_replace(
            $match[1],
            preg_replace(
                '#href=.*?(?:(?:alert|prompt|confirm)(?:\(|&\#40;)|javascript:|livescript:|mocha:|charset=|window\.|document\.|\.cookie|<script|<xss|data\s*:)#si',
                '',
                $this->_filter_attributes(str_replace(['<', '>'], '', $match[1]))
            ),
            $match[0]
        );
    }

    protected function _js_img_removal($match)
    {
        return str_replace(
            $match[1],
            preg_replace(
                '#src=.*?(?:(?:alert|prompt|confirm)(?:\(|&\#40;)|javascript:|livescript:|mocha:|charset=|window\.|document\.|\.cookie|<script|<xss|base64\s*,)#si',
                '',
                $this->_filter_attributes(str_replace(['<', '>'], '', $match[1]))
            ),
            $match[0]
        );
    }

    protected function _convert_attribute($match)
    {
        return str_replace(['>', '<', '\\'], ['&gt;', '&lt;', '\\\\'], $match[0]);
    }

    protected function _filter_attributes($str)
    {
        $out = '';

        if (preg_match_all('#\s*[a-z\-]+\s*=\s*(\042|\047)([^\\1]*?)\\1#is', $str, $matches)) {
            foreach ($matches[0] as $match) {
                $out .= preg_replace("#/\*.*?\*/#s", '', $match);
            }
        }

        return $out;
    }

    protected function _decode_entity($match)
    {
        // Protect GET variables in URLs
        // 901119URL5918AMP18930PROTECT8198
        $match = preg_replace('|\&([a-z\_0-9\-]+)\=([a-z\_0-9\-/]+)|i', $this->xss_hash().'\\1=\\2', $match[0]);

        // Decode, then un-protect URL GET vars
        return str_replace(
            $this->xss_hash(),
            '&',
            $this->entity_decode($match, 'UTF-8')
        );
    }

    protected function _do_never_allowed($str)
    {
        $str = str_replace(array_keys($this->_never_allowed_str), $this->_never_allowed_str, $str);

        foreach ($this->_never_allowed_regex as $regex) {
            $str = preg_replace('#'.$regex.'#is', '[removed]', $str);
        }

        return $str;
    }
}
