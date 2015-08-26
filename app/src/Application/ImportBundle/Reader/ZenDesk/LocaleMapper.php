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
     * Returns DeskPRO locale by ZenDesk locale code
     *
     * @param string $zd_locale
     *
     * @return string
     * @throws \RuntimeException
     */
    public static function getLocale($zd_locale)
    {
        $mapping = self::getLocaleCodesMapping();
        $code    = strtolower($zd_locale);

        if (isset($mapping[$code])) {
            return $mapping[$code];
        }

        throw new \RuntimeException(sprintf('Locale name not found by `%s`', $zd_locale));
    }

    /**
     * ZenDesk locales
     *
     * @return array
     */
    public static function getLocaleCodesMapping()
    {
        return array(
            'ar-eg'  => 'ar',    // Arabic (Egypt)
            'ar'     => 'ar',    // Arabic
            'ms'     => 'en_US', // Bahasa Melayu
            'ca'     => 'en_US', // Català (Catalan)
            'sr-me'  => 'en_US', // Crnogorski (Montenegrin)
            'da'     => 'da',    // Dansk
            'de'     => 'de',    // Deutsch
            'de-at'  => 'de',    // Deutsch (Austria)
            'de-ch'  => 'de',    // Deutsch (Switzerland)
            'et'     => 'en_US', // Eesti keel (Estonian)
            'en-us'  => 'en_US', // English
            'en-au'  => 'en_US', // English (AU)
            'en-ca'  => 'en_US', // English (Canada)
            'en-ie'  => 'en_US', // English (IE)
            'en-gb'  => 'en_GB', // English (UK)
            'es'     => 'es_ES', // Español
            'es-es'  => 'es_ES', // Español (España)
            'es-419' => 'es_ES', // Español (Latinoamérica)
            'fil'    => 'en_US', // Filipino
            'fr'     => 'fr',    // Français
            'fr-be'  => 'fr',    // Français (Belgium)
            'fr-ca'  => 'fr',    // Français (Canada)
            'fr-ch'  => 'fr',    // Français (Switzerland)
            'hr'     => 'en_US', // Hrvatski
            'id'     => 'en_US', // Indonesian
            'it'     => 'it',    // Italiano
            'lv'     => 'en_US', // Latvian
            'lt'     => 'en_US', // Lietuvių kalba
            'hu'     => 'hu',    // Magyar
            'nl-be'  => 'en_US', // Nederlands (Belgium)
            'nl'     => 'nl',    // Nederlands (Dutch)
            'no'     => 'no',    // Norsk
            'pl'     => 'pl',    // Polski (Polish)
            'pt-br'  => 'pt',    // Português (Brasil)
            'pt'     => 'pt',    // Português (Portugal)
            'ro'     => 'ro',    // Romana
            'sk'     => 'sk',    // Slovak
            'sl'     => 'en_US', // Slovenian
            'sr'     => 'en_US', // Srpski
            'fi'     => 'fi',    // Suomi (Finnish)
            'sv'     => 'sv',    // Svenska
            'th'     => 'en_US', // Thai (ไทย)
            'tr'     => 'tr',    // Türkçe
            'vi'     => 'en_US', // Vietnamese
            'is'     => 'en_US', // Íslenska
            'cs'     => 'en_US', // Čeština
            'el'     => 'en_US', // Ελληνικά (Greek)
            'ru'     => 'ru',    // Русский
            'uk'     => 'ru',    // Українська
            'he'     => 'en_US', // Hebrew
            'hi'     => 'en_US', // हिंदी
            'ja'     => 'ja',    // 日本語 (Japanese)
            'zh-cn'  => 'en_US', // 简体中文 (Simplified Chinese)
            'zh-tw'  => 'en_US', // 繁體中文 (Traditional Chinese)
            'ko'     => 'ko',    // 한국어 (Korean)
        );
    }
}
