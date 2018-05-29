<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SendmailBundle\Twig\PostRenderFilter;

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

        $origCode = $code;

        // Separate out subject
        $subj = null;
        if (preg_match('#<dp:subject>(.*?)</dp:subject>#s', $code, $matches)) {
            $subj = $matches[0];
            $code = preg_replace('#<dp:subject>(.*?)</dp:subject>#s', '', $code);
        }

        // Dont run emog on messages, only on the email template
        // This takes out email messages and replaces them with tokens until we're done
        $saveBlocks = [];
        // regex `s` modifier required here to match `\n` within `.`
        // message might have new lines (for example in case of signature)
        $code = preg_replace_callback('#<!-- DP_MESSAGE_BEGIN -->(.*?)<!-- DP_MESSAGE_END -->#s', function ($m) use (&$saveBlocks) {
            $rand = uniqid('DPBLOCK', true);
            $saveBlocks[$rand] = $m[0];

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
            $css  = file_get_contents(DP_WEB_ROOT.'/pub/src/DeskPRO/Bundle/AppBundle/Resources/style/emails/zurb-foundation.css');
            $emog = new Emogrifier($code, $css);
            $code = $emog->emogrify();
        }
        $code = Strings::postDomDocument($code);

        foreach ($saveBlocks as $id => $block) {
            // Replace <p> with <div /> in messages
            $block = preg_replace('/<p\s([^>]*)|<p()>/', '<div$1>', $block);
            $block = str_replace('</p>', '</div>', $block);
            $code  = str_replace($id, $block, $code);
        }

        if (!$code) {
            return $origCode;
        }

        if ($subj) {
            $code = $subj."\n".$code;
        }

        if (strpos($name, 'SendmailBundle:emails_user:') === 0) {
            $code = str_replace('DP_TOP_MARK', 'DP_TOP_MARK DP_USER_EMAIL', $code);
        }

        return $code;
    }
}
