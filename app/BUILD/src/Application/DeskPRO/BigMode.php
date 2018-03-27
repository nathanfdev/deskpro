<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO;

class BigMode
{
    const PERSON_AUTOCOMPLETE           = 'person_autocomplete';
    const PERSON_SEARCH_PREFIX_WILDCARD = 'person_search_prefix_wildcard';

    public static function isBigMode($context)
    {
        switch ($context) {
            case self::PERSON_AUTOCOMPLETE:
                return App::getSetting('core_tablecounts.people') > 200000;

            case self::PERSON_SEARCH_PREFIX_WILDCARD:
                return App::getSetting('core_tablecounts.people') > 200000;
        }

        return false;
    }
}
