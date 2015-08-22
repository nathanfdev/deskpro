<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\Reader\ZenDesk;

/**
 * Class LocaleMapper
 * @package Application\ImportBundle\Reader\ZenDesk
 */
class LocaleMapper
{
    /**
     * @return array
     */
    public static function localeCodesMapping()
    {
        return array(
            'ar-EG'  => array(
                'id'   => 1287,
                'name' => ' Arabic (Egypt)',
            ),
            'ar'     => array(
                'id'   => 66,
                'name' => 'Arabic',
            ),
            'ms'     => array(
                'id'   => 1307,
                'name' => 'Bahasa Melayu',
            ),
            'ca'     => array(
                'id'   => 1075,
                'name' => 'Català (Catalan)',
            ),
            'sr-ME'  => array(
                'id'   => 1298,
                'name' => 'Crnogorski (Montenegrin)',
            ),
            'da'     => array(
                'id'   => 1000,
                'name' => 'Dansk',
            ),
            'de'     => array(
                'id'   => 8,
                'name' => 'Deutsch',
            ),
            'de-AT'  => array(
                'id'   => 1294,
                'name' => 'Deutsch (Austria)',
            ),
            'de-CH'  => array(
                'id'   => 1295,
                'name' => 'Deutsch (Switzerland)',
            ),
            'et'     => array(
                'id'   => 101,
                'name' => 'Eesti keel (Estonian)',
            ),
            'en-US'  => array(
                'id'   => 1,
                'name' => 'English',
            ),
            'en-au'  => array(
                'id'   => 1277,
                'name' => 'English (AU)',
            ),
            'en-CA'  => array(
                'id'   => 1181,
                'name' => 'English (Canada)',
            ),
            'en-ie'  => array(
                'id'   => 1279,
                'name' => 'English (IE)',
            ),
            'en-GB'  => array(
                'id'   => 1176,
                'name' => 'English (UK)',
            ),
            'es'     => array(
                'id'   => 2,
                'name' => 'Español',
            ),
            'es-ES'  => array(
                'id'   => 1186,
                'name' => 'Español (España)',
            ),
            'es-419' => array(
                'id'   => 1194,
                'name' => 'Español (Latinoamérica)',
            ),
            'fil'    => array(
                'id'   => 47,
                'name' => 'Filipino',
            ),
            'fr'     => array(
                'id'   => 16,
                'name' => 'Français',
            ),
            'fr-be'  => array(
                'id'   => 1291,
                'name' => 'Français (Belgium)',
            ),
            'fr-CA'  => array(
                'id'   => 1187,
                'name' => 'Français (Canada)',
            ),
            'fr-CH'  => array(
                'id'   => 1292,
                'name' => 'Français (Switzerland)',
            ),
            'hr'     => array(
                'id'   => 74,
                'name' => 'Hrvatski',
            ),
            'id'     => array
            (
                'id'   => 77,
                'name' => 'Indonesian',
            ),
            'it'     => array(
                'id'   => 22,
                'name' => 'Italiano',
            ),
            'lv'     => array(
                'id'   => 1101,
                'name' => 'Latvian',
            ),
            'lt'     => array(
                'id'   => 1092,
                'name' => 'Lietuvių kalba',
            ),
            'hu'     => array(
                'id'   => 1009,
                'name' => 'Magyar',
            ),
            'nl-be'  => array(
                'id'   => 1293,
                'name' => 'Nederlands (Belgium)',
            ),
            'nl'     => array(
                'id'   => 1005,
                'name' => 'Nederlands (Dutch)',
            ),
            'no'     => array(
                'id'   => 34,
                'name' => 'Norsk',
            ),
            'pl'     => array(
                'id'   => 13,
                'name' => 'Polski (Polish)',
            ),
            'pt-BR'  => array(
                'id'   => 19,
                'name' => 'Português (Brasil)',
            ),
            'pt'     => array(
                'id'   => 1011,
                'name' => 'Português (Portugal)',
            ),
            'ro'     => array(
                'id'   => 23,
                'name' => 'Romana',
            ),
            'sk'     => array(
                'id'   => 1003,
                'name' => 'Slovak',
            ),
            'sl'     => array(
                'id'   => 72,
                'name' => 'Slovenian',
            ),
            'sr'     => array(
                'id'   => 1150,
                'name' => 'Srpski',
            ),
            'fi'     => array(
                'id'   => 84,
                'name' => 'Suomi (Finnish)',
            ),
            'sv'     => array(
                'id'   => 92,
                'name' => 'Svenska',
            ),
            'th'     => array(
                'id'   => 81,
                'name' => 'Thai (ไทย)',
            ),
            'tr'     => array(
                'id'   => 88,
                'name' => 'Türkçe',
            ),
            'vi'     => array(
                'id'   => 26,
                'name' => 'Vietnamese',
            ),
            'is'     => array(
                'id'   => 24,
                'name' => 'Íslenska',
            ),
            'cs'     => array(
                'id'   => 78,
                'name' => 'Čeština',
            ),
            'el'     => array(
                'id'   => 93,
                'name' => 'Ελληνικά (Greek)',
            ),
            'ru'     => array(
                'id'   => 27,
                'name' => 'Русский',
            ),
            'uk'     => array(
                'id'   => 1173,
                'name' => 'Українська',
            ),
            'he'     => array(
                'id'   => 30,
                'name' => 'עִבְרִית (Hebrew)',
            ),
            'hi'     => array(
                'id'   => 1303,
                'name' => 'हिंदी',
            ),
            'ja'     => array(
                'id'   => 67,
                'name' => '日本語 (Japanese)',
            ),
            'zh-CN'  => array(
                'id'   => 10,
                'name' => '简体中文 (Simplified Chinese)',
            ),
            'zh-TW'  => array(
                'id'   => 9,
                'name' => '繁體中文 (Traditional Chinese)',
            ),
            'ko'     => array(
                'id'   => 69,
                'name' => '한국어 (Korean)',
            ),
        );
    }
}
