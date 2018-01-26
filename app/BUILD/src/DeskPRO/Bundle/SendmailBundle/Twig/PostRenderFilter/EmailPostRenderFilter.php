<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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
        $code       = preg_replace_callback('#<!-- DP_MESSAGE_BEGIN -->(.*?)<!-- DP_MESSAGE_END -->#', function ($m) use (&$saveBlocks) {
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
            $code = str_replace($id, $block, $code);
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
