<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Cutter\Def;

use Orb\Util\Strings;

class Generic implements ForwardDef, QuoteDef
{
    /**
     * @var array
     */
    protected $fwd_patterns;

    /**
     * @var array|array
     */
    protected $translate_map;

    public function __construct()
    {
        $this->translate_map = new \Application\DeskPRO\Config\UserFileConfig('cut-patterns-translate');
        $this->translate_map = $this->translate_map->all();

        $this->fwd_patterns = new \Application\DeskPRO\Config\UserFileConfig('cut-fwd-patterns');
        $this->fwd_patterns = $this->fwd_patterns->all();
    }

    /**
     * Tries to split a message by looking at the first sequence of From/To/Date/Subject headers
     * to mark the beginning.
     *
     * @param $body
     *
     * @return array|null
     */
    public function splitFromFirstHeaderText($body)
    {
        $body = Strings::standardEol($body);

        $found      = 0;
        $start_line = null;

        // - Try to fix From that has [email address] on a new line after From:
        // - Normalise labels that have starts around them: *From:* which can happen when clients convert html to text (eg postboxapp)
        foreach ($this->translate_map as $set) {
            $pattern  = '#^(%From%): ([^\n\r]+)\s*(\[|<)(.*?)(\]|>)#m';
            $pattern2 = '#^\*(%From%|%Sent%|%To%|%Date%|%Subject%|%CC%|%BCC%):\*#mi';
            $pattern3 = '#^(\s*>+\s*)\*(%From%|%Sent%|%To%|%Date%|%Subject%|%CC%|%BCC%):\*#mi';

            foreach ($set as $f => $r) {
                $pattern  = str_replace($f, $r, $pattern);
                $pattern2 = str_replace($f, $r, $pattern2);
                $pattern3 = str_replace($f, $r, $pattern3);
            }

            $body = preg_replace($pattern, '$1: $2 <$4>', $body);
            $body = preg_replace($pattern2, '$1:', $body);
            $body = preg_replace($pattern3, '$1$2:', $body);
        }

        $body = explode("\n", $body);

        foreach ($this->translate_map as $set) {
            foreach ($body as $ln => $l) {
                $l = preg_replace('#^\s*>+\s*#', '', $l);

                $pattern = '#^(%From%|%Sent%|%To%|%Date%|%Subject%|%CC%|%BCC%):(.*?)$#i';
                foreach ($set as $f => $r) {
                    $pattern = str_replace($f, $r, $pattern);
                }

                if (preg_match($pattern, $l)) {
                    if ($start_line === null) {
                        $start_line = $ln;
                    }
                    ++$found;
                    if ($found >= 2) {
                        break;
                    }
                } else {
                    $found      = 0;
                    $start_line = null;
                }
            }

            if ($found >= 2) {
                break;
            }
            $start_line = null;
            $found      = 0;
        }

        // If we didnt find at least two of the four headers,
        // consider it a no-match
        if ($found < 2) {
            return;
        }

        $parts = [
            array_slice($body, 0, $start_line),
            array_slice($body, $start_line),
        ];

        $parts[0] = implode("\n", $parts[0]);
        $parts[1] = implode("\n", $parts[1]);

        return $parts;
    }

    /**
     * Get an array of info from the forwarded block.
     *
     * @param string $body
     * @param bool   $is_html
     *
     * @return array
     */
    public function getForwardInfo($body, $is_html = false)
    {
        $forward_data = [
            'message_body'        => null,
            'fwd_message_body'    => null,
            'fwd_message_headers' => null,
            'fwd_from_email'      => null,
            'fwd_from_name'       => null,
            'fwd_cc_addresses'    => null,
            'fwd_cc_unknown'      => null,
        ];

        $parts_pattern = null;
        foreach ($this->fwd_patterns as $pattern) {
            $parts_pattern = preg_split($pattern, $body, 2);
            if ($parts_pattern && count($parts_pattern) == 2) {
                break;
            }

            $parts_pattern = null;
        }

        // Fallback on cutting based on standard message headers (From etc)
        $parts_generic = $this->splitFromFirstHeaderText($body);

        // No suitable cutline
        if (!$parts_generic || count($parts_generic) != 2) {
            $parts_generic = null;
        }

        if ($parts_pattern && $parts_generic) {
            // If both the pattern cut and the generic cut
            // matched a pattern, we'll take the one "furthest up"
            if (strlen($parts_pattern[0]) < strlen($parts_generic[0])) {
                $parts = $parts_pattern;
            } else {
                $parts = $parts_generic;
            }
        } elseif ($parts_pattern) {
            $parts = $parts_pattern;
        } elseif ($parts_generic) {
            $parts = $parts_generic;
        } else {
            // Could not cut
            return $forward_data;
        }

        $forward_data['message_body'] = trim($parts[0]);

        //------------------------------
        // Split the forwarded message into
        // a header section and a body
        //------------------------------

        // Dequote
        $fwd_message_body = explode("\n", Strings::standardEol($parts[1]));
        foreach ($fwd_message_body as &$l) {
            $l = preg_replace('#^\s*>+\s*#', '', $l);
            $l = trim($l);
        }
        unset($l);

        $fwd_message_body = implode("\n", $fwd_message_body);

        if ($is_html) {
            $fwd_message_body = str_replace(['<br />', '<br/>'], '<br>', $fwd_message_body);
            $fwd_parts        = preg_split('#<br>\s*<br>#i', $fwd_message_body, 2);
        } else {
            $fwd_parts = preg_split('#\n{2}#i', $fwd_message_body, 2);
        }

        if (count($fwd_parts) != 2) {
            $fwd_parts = [$fwd_message_body, $fwd_message_body];
        }

        $forward_data['fwd_message_headers'] = trim($fwd_parts[0]);
        $forward_data['fwd_message_body']    = trim($fwd_parts[1]);

        if (!$forward_data['fwd_message_body']) {
            $forward_data['fwd_message_body'] = $fwd_message_body;
        }

        //------------------------------
        // Try to read the email address from the fwd headers
        //------------------------------

        foreach ($this->translate_map as $set) {
            $patterns     = [];
            $replacements = [];
            foreach ($set as $replacement => $pattern) {
                $patterns[]                          = "#^($pattern)(?=:\s+)#mi";
                $replacements[]                      = trim($replacement, '%');
                $forward_data['fwd_message_headers'] = preg_replace(
                    $patterns,
                    $replacements,
                    $forward_data['fwd_message_headers']
                );
            }
        }
        $pos = stripos($forward_data['fwd_message_headers'], 'from');
        if ($pos === false) {
            return $forward_data;
        }

        $pos2 = strpos($forward_data['fwd_message_headers'], "\n", $pos);
        if ($pos2 === false) {
            // If no newline, means we only have 1 line so the pos2 is at the end of that line
            $pos2 = strlen($forward_data['fwd_message_headers']);
        }

        $from_str = substr($forward_data['fwd_message_headers'], $pos, $pos2 - $pos);
        $m        = null;

        if (preg_match('#mailto:(.*?)@([a-zA-Z0-9\.\-_]+)#', $from_str.' ', $m)) {
            $forward_data['fwd_from_email'] = $m[1].'@'.$m[2];
        } elseif (preg_match('#(<|\[|\()(\S*)@([a-zA-Z0-9\.\-_]+)(>|\]|\))#i', $from_str, $m)) {
            $forward_data['fwd_from_email'] = $m[2].'@'.$m[3];
        } elseif (preg_match('#[\w]+:\s*?(.*?)@([a-zA-Z0-9\.\-_]+)#i', $from_str, $m)) {
            $forward_data['fwd_from_email'] = $m[1].'@'.$m[2];
        } elseif (preg_match('#\s(.*?)@([a-zA-Z0-9\.\-]+)\s#i', $from_str, $m)) {
            $forward_data['fwd_from_email'] = $m[1].'@'.$m[2];
        }

        if ($forward_data['fwd_from_email']) {
            $forward_data['fwd_from_email'] = trim($forward_data['fwd_from_email']);

            $pos  = strpos($from_str, $forward_data['fwd_from_email']);
            $name = substr($from_str, 0, $pos);
            if (preg_match('#^[\w]+:(.*?)(<|\[|\()#', $name, $m)) {
                $name = $m[1];
            } elseif (preg_match('#^[\w]+:(.*?)#', $name, $m)) {
                $name = $m[1];
            }
            $name                          = trim($name);
            $forward_data['fwd_from_name'] = $name;
        }

        //------------------------------
        // Try to read CC addresses
        //------------------------------

        $cc_line = Strings::extractRegexMatch('#^(CC|Cc): (.*?)$#m', $forward_data['fwd_message_headers'], 2);
        if ($cc_line) {
            $emails = \ezcMailTools::parseEmailAddresses($cc_line, 'UTF-8');
            if ($emails) {
                $forward_data['fwd_cc_addresses'] = [];
                foreach ($emails as $e) {
                    $forward_data['fwd_cc_addresses'][] = [
                        'name'  => $e->name,
                        'email' => $e->email,
                    ];
                }
            } else {
                $forward_data['fwd_cc_unknown'] = $cc_line;
            }
        }

        return $forward_data;
    }

    /**
     * Cut out the quote block.
     *
     * @param string $body
     * @param bool   $is_html
     *
     * @return string
     */
    public function cutQuoteBlock($body, $is_html = false)
    {
        // Remove DP_PREVIEW_TEXT stuff which looks like:
        // <div id="DP_PREVIEW_TEXT_MARK" class="DP_PREVIEW_TEXT_MARK" ...>text</div>
        $body = preg_replace('#\s*<div[^>]*DP_PREVIEW_TEXT_MARK[^>]*>(.*?)</div>\s*#is', '', $body);

        // Have cuts in the form of <div class="DP_TOP_MARK"> or <!--DP_TOP_MARK-->
        $pos = strpos($body, 'DP_BLOCKQUOTE_BEGIN');
        if ($pos === false) {
            $pos = strpos($body, 'DP_TOP_MARK');
            if ($pos === false) {
                $pos = strpos($body, 'DP_TOP_MARK_USER');
                if ($pos === false) {
                    // Try to detect '=== REPLY ABOVE THIS LINE ===' bits
                    $matches = [];
                    if (preg_match(
                        '#(?:=(?:3D)?){3}(?:\s|&nbsp;)*[^=]+(?: \[.+\])?(?:\s|&nbsp;)*(?:=(?:3D)?){3}#',
                        $body,
                        $matches,
                        \PREG_OFFSET_CAPTURE
                    )) {
                        $pos = $matches[0][1];
                    }

                    if ($pos === false) {
                        return $body;
                    }
                }
            }
        }

        $body = trim(substr($body, 0, $pos));

        // We also want to cut from is the < character, so we dont
        // cut mid-way into an html tag
        if ($is_html) {
            $pos = strrpos($body, '<');
            if ($pos) {
                $body = substr($body, 0, $pos);
            }
        } else {
            $body = rtrim($body, '> ');
        }

        // Cut off "===" that would preceded the cut marker
        $body = preg_replace('#\s*===\s*$#s', '', $body);
        $body = preg_replace('#\s*===\s*<[a-zA-Z0-9_\-/]+>\s*$#s', '', $body);

        return $body;
    }

    /**
     * Try to cut out text below the email as well.
     *
     * @param string $body
     * @param bool   $is_html
     *
     * @return string
     */
    public function cutBottomBlock($body, $is_html = false)
    {
        if (!$is_html) {
            return '';
        }

        // Have cuts in the form of <div class="DP_BOTTOM_MARK"> or <!--DP_BOTTOM_MARK-->
        $body_btm = '';
        $pos      = strpos($body, 'DP_BOTTOM_MARK');
        if ($pos !== false) {
            $body_btm = substr($body, $pos);
            if ($is_html) {
                $pos = strpos($body_btm, '>');
                if ($pos) {
                    $body_btm = substr($body_btm, $pos + 1);
                }
            }

            if ($body_btm) {
                $body_btm = preg_replace('#\s*</body>\s*</html>\s*#', '', $body_btm);

                // The empty <a> used for the DP_BOTTOM_MARK marker
                $body_btm = preg_replace('#\s*<a[^>]*>\s*</a>\s*#', '', $body_btm);
            }

            $body_btm = trim($body_btm);
        }

        if ($body_btm && (!$is_html || strip_tags($body_btm))) {
            $body_btm = "\n\n".$body_btm;
        } else {
            $body_btm = '';
        }

        return $body_btm;
    }
}
