<?php

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
