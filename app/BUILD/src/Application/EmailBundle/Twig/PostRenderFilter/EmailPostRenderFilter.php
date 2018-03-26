<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Twig\PostRenderFilter;

use Orb\Util\Strings;
use Pelago\Emogrifier;

class EmailPostRenderFilter extends AbstractPostRenderFilter
{
    public function process($name, $code)
    {
        $m = null;
        if (!preg_match_all('#<style[^>]*>(.*?)</style>#s', $code, $m, \PREG_PATTERN_ORDER)) {
            return $code;
        }

        $orig_code = $code;

        // Separate out subject
        $parts = explode('___DP___SUBJECT___SEP___', $code, 2);
        $subj  = null;
        if (count($parts) == 2) {
            $subj = trim($parts[0]);
            $code = trim($parts[1]);
        }

        // Dont run emog on messages, only on the email template
        // This takes out email messages and replaces them with tokens until we're done
        $save_blocks = [];
        $code        = preg_replace_callback('#<!-- DP_MESSAGE_BEGIN -->(.*?)<!-- DP_MESSAGE_END -->#', function ($m) use (&$save_blocks) {
            $rand = uniqid('DPBLOCK', true);
            $save_blocks[$rand] = $m[0];

            return $rand;
        }, $code);

        $css = implode("\n", $m[1]);
        foreach ($m[0] as $find) {
            $code = str_replace($find, '', $code);
        }

        $code = Strings::preDomDocument($code);
        $emog = new Emogrifier($code, $css);
        try {
            $code = $emog->emogrify();
        } catch (\Exception $e) {
            // In case of error with css with failover on default css
            $css  = file_get_contents(DP_ROOT.'/src/Application/DeskPRO/Resources/views/emails_common/email-css.css.twig');
            $emog = new Emogrifier($code, $css);
            $code = $emog->emogrify();
        }
        $code = Strings::postDomDocument($code);

        foreach ($save_blocks as $id => $block) {
            // Replace <p> with <div /> in messages
            $block = preg_replace('/<p([^>]*)>/', '<div$1>', $block);
            $block = str_replace('</p>', '</div>', $block);
            $code  = str_replace($id, $block, $code);
        }

        if (!$code) {
            return $orig_code;
        }

        if ($subj) {
            $code = $subj.'___DP___SUBJECT___SEP___'.$code;
        }

        if (strpos($name, 'DeskPRO:emails_user:') === 0) {
            $code = str_replace('DP_TOP_MARK', 'DP_TOP_MARK DP_USER_EMAIL', $code);
        }

        return $code;
    }
}
