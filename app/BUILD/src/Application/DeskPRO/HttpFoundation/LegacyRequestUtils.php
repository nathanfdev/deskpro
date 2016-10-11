<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace Application\DeskPRO\HttpFoundation;

use Orb\Util\Strings;
use Symfony\Component\HttpFoundation\Request;

class LegacyRequestUtils
{
    private function __construct()
    {
    }

    /**
     * @param Request $request
     *
     * @return string|null
     */
    public static function readReturnParam(Request $request)
    {
        if ('application/json' === $request->getContentType()) {
            if ($data = json_decode((string) $request->getContent(), 1)) {
                if (!$return = @$data['return']) {
                    return;
                }
            }
        }

        $return = $request->get('return');
        if (!$return || !is_string($return)) {
            return;
        }

        if (Strings::containsInvisibleCharacters($return) || Strings::containsLineBreaks($return)) {
            return;
        }

        if (strpos($return, '\\') !== false || strpos($return, '@') !== false) {
            if ('/' !== $return[0] || '//' === substr($return, 0, 2)) {
                return;
            }
        }

        return $return;
    }
}
