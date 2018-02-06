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

namespace Application\DeskPRO\Twig\PostRenderFilter;

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
        $code = $emog->emogrify();
        $code = Strings::postDomDocument($code);

        foreach ($save_blocks as $id => $block) {
            // Replace <p> with <div /> in messages
            $block = preg_replace('/<p([^>]*)>/', '<div$1>', $block);
            $block = str_replace('</p>', '</div>', $block);
            $code  = str_replace($id, $block, $code);
        }

        $code = preg_replace_callback('#<table([^>]*)>#i', function ($m) {
            if (strpos($m[0], 'dp_message_table') === false) {
                return $m[0];
            }

            return '<table border="1" cellspacing="0" cellpadding="4">';
        }, $code);

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
