<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace Application\ImportBundle\Reader\OsTicket;

use Exception;

/**
 * OsTicket timezone mapper.
 *
 * Values in ost_timezones:
 *
 * GMT = -12.0, 'Eniwetok, Kwajalein',
 * GMT = -11.0, 'Midway Island, Samoa',
 * GMT = -10.0, 'Hawaii',
 * GMT = -9.0, 'Alaska',
 * GMT = -8.0, 'Pacific Time (US & Canada)',
 * GMT = -7.0, 'Mountain Time (US & Canada)',
 * GMT = -6.0, 'Central Time (US & Canada), Mexico City',
 * GMT = -5.0, 'Eastern Time (US & Canada), Bogota, Lima',
 * GMT = -4.0, 'Atlantic Time (Canada), Caracas, La Paz',
 * GMT = -3.5, 'Newfoundland',
 * GMT = -3.0, 'Brazil, Buenos Aires, Georgetown',
 * GMT = -2.0, 'Mid-Atlantic',
 * GMT = -1.0, 'Azores, Cape Verde Islands',
 * GMT = 0.0, 'Western Europe Time, London, Lisbon, Casablanca',
 * GMT = 1.0, 'Brussels, Copenhagen, Madrid, Paris',
 * GMT = 2.0, 'Kaliningrad, South Africa',
 * GMT = 3.0, 'Baghdad, Riyadh, Moscow, St. Petersburg',
 * GMT = 3.5, 'Tehran',
 * GMT = 4.0, 'Abu Dhabi, Muscat, Baku, Tbilisi',
 * GMT = 4.5, 'Kabul',
 * GMT = 5.0, 'Ekaterinburg, Islamabad, Karachi, Tashkent',
 * GMT = 5.5, 'Bombay, Calcutta, Madras, New Delhi',
 * GMT = 6.0, 'Almaty, Dhaka, Colombo',
 * GMT = 7.0, 'Bangkok, Hanoi, Jakarta',
 * GMT = 8.0, 'Beijing, Perth, Singapore, Hong Kong',
 * GMT = 9.0, 'Tokyo, Seoul, Osaka, Sapporo, Yakutsk',
 * GMT = 9.5, 'Adelaide, Darwin',
 * GMT = 10.0, 'Eastern Australia, Guam, Vladivostok',
 * GMT = 11.0, 'Magadan, Solomon Islands, New Caledonia',
 * GMT = 12.0, 'Auckland, Wellington, Fiji, Kamchatka';
 *
 * Class TimeZoneMapper
 */
class TimeZoneMapper
{
    /**
     * Returns DateTimeZone name by offset and OsTicket timezone name.
     *
     * @param float  $offset
     * @param string $osticket_name
     *
     * @throws Exception
     *
     * @return string
     */
    public static function getTimeZoneName($offset, $osticket_name = null)
    {
        $timezones = self::getTimezonesByOffset($offset);
        if (empty($timezones)) {
            throw new Exception(sprintf('Unable to get timezones by offset `%s`', $offset));
        }

        $timezone = null;
        if ($osticket_name) {
            foreach ($timezones as $timezone) {
                $timezone_name = str_replace('_', ' ', $timezone['name']);
                $timezone_name = preg_replace('#.*/(\w+)$#', '\\1', $timezone_name);

                if (strpos($osticket_name, $timezone_name) !== false) {
                    break;
                }
            }
        }

        if (!$timezone) {
            $timezone = array_shift($timezones);
        }

        return $timezone['name'];
    }

    /**
     * Returns filtered collection of timezone names by offset.
     *
     * @param string $offset
     *
     * @return array
     */
    public static function getTimezonesByOffset($offset)
    {
        $timezones = array();
        $offset    = (float) $offset;

        foreach (self::getTimezones() as $timezone) {
            if ($offset === (float) $timezone['offset']) {
                $timezones[] = $timezone;
            }
        }

        return $timezones;
    }

    /**
     * Returns timezones with offsets.
     *
     * @return array
     */
    public static function getTimezones()
    {
        return array(
            array('name' => 'Africa/Juba',                    'offset' => +3),
            array('name' => 'America/Creston',                'offset' => -7),
            array('name' => 'America/Kralendijk',             'offset' => -4),
            array('name' => 'America/Lower_Princes',          'offset' => -4),
            array('name' => 'America/Metlakatla',             'offset' => -8),
            array('name' => 'America/North_Dakota/Beulah',    'offset' => -5),
            array('name' => 'America/Sitka',                  'offset' => -8),
            array('name' => 'Asia/Hebron',                    'offset' => +2),
            array('name' => 'Pacific/Chuuk',                  'offset' => +10),
            array('name' => 'Pacific/Pohnpei',                'offset' => +11),
            array('name' => 'Africa/El_Aaiun',                'offset' => 0),
            array('name' => 'America/St_Barthelemy',          'offset' => -4),
            array('name' => 'America/St_Johns',               'offset' => -2.5),
            array('name' => 'America/St_Kitts',               'offset' => -4),
            array('name' => 'America/St_Lucia',               'offset' => -4),
            array('name' => 'America/St_Thomas',              'offset' => -4),
            array('name' => 'America/St_Vincent',             'offset' => -4),
            array('name' => 'America/La_Paz',                 'offset' => -4),
            array('name' => 'America/La_Paz',                 'offset' => -3.5), // GMT = -3.5, 'Newfoundland'
            array('name' => 'America/El_Salvador',            'offset' => -6),
            array('name' => 'Asia/Ho_Chi_Minh',               'offset' => +7),
            array('name' => 'Atlantic/St_Helena',             'offset' => 0),
            array('name' => 'Australia/Currie',               'offset' => +10),
            array('name' => 'Australia/Eucla',                'offset' => +8.75),
            array('name' => 'Australia/Lindeman',             'offset' => +10),
            array('name' => 'Australia/Lord_Howe',            'offset' => +10.5),
            array('name' => 'Australia/Adelaide',             'offset' => +9.5),
            array('name' => 'Australia/Brisbane',             'offset' => +10),
            array('name' => 'Australia/Broken_Hill',          'offset' => +9.5),
            array('name' => 'Australia/Darwin',               'offset' => +9.5),
            array('name' => 'Australia/Melbourne',            'offset' => +10),
            array('name' => 'Australia/Perth',                'offset' => +8),
            array('name' => 'Australia/Sydney',               'offset' => +10),
            array('name' => 'Australia/Hobart',               'offset' => +10),
            array('name' => 'Asia/Aqtobe',                    'offset' => +5),
            array('name' => 'Asia/Anadyr',                    'offset' => +12),
            array('name' => 'Asia/Choibalsan',                'offset' => +8),
            array('name' => 'Asia/Kashgar',                   'offset' => +8),
            array('name' => 'Asia/Kolkata',                   'offset' => +5.5),
            array('name' => 'Asia/Macau',                     'offset' => +8),
            array('name' => 'Asia/Qyzylorda',                 'offset' => +6),
            array('name' => 'Asia/Aden',                      'offset' => +3),
            array('name' => 'Asia/Aqtau',                     'offset' => +5),
            array('name' => 'Asia/Almaty',                    'offset' => +6),
            array('name' => 'Asia/Amman',                     'offset' => +3),
            array('name' => 'Asia/Ashgabat',                  'offset' => +5),
            array('name' => 'Asia/Baghdad',                   'offset' => +3),
            array('name' => 'Asia/Baku',                      'offset' => +5),
            array('name' => 'Asia/Bangkok',                   'offset' => +7),
            array('name' => 'Asia/Bahrain',                   'offset' => +3),
            array('name' => 'Asia/Beirut',                    'offset' => +3),
            array('name' => 'Asia/Bishkek',                   'offset' => +6),
            array('name' => 'Asia/Brunei',                    'offset' => +8),
            array('name' => 'Asia/Vladivostok',               'offset' => +11),
            array('name' => 'Asia/Vientiane',                 'offset' => +7),
            array('name' => 'Asia/Gaza',                      'offset' => +2),
            array('name' => 'Asia/Hong_Kong',                 'offset' => +8),
            array('name' => 'Asia/Dhaka',                     'offset' => +6),
            array('name' => 'Asia/Damascus',                  'offset' => +3),
            array('name' => 'Asia/Jakarta',                   'offset' => +7),
            array('name' => 'Asia/Jayapura',                  'offset' => +9),
            array('name' => 'Asia/Dili',                      'offset' => +9),
            array('name' => 'Asia/Dubai',                     'offset' => +4),
            array('name' => 'Asia/Dushanbe',                  'offset' => +5),
            array('name' => 'Asia/Yerevan',                   'offset' => +4),
            array('name' => 'Asia/Jerusalem',                 'offset' => +3),
            array('name' => 'Asia/Irkutsk',                   'offset' => +9),
            array('name' => 'Asia/Kabul',                     'offset' => +4.5),
            array('name' => 'Asia/Kamchatka',                 'offset' => +12),
            array('name' => 'Asia/Karachi',                   'offset' => +5),
            array('name' => 'Asia/Qatar',                     'offset' => +3),
            array('name' => 'Asia/Kathmandu',                 'offset' => +5.75),
            array('name' => 'Asia/Hovd',                      'offset' => +7),
            array('name' => 'Asia/Colombo',                   'offset' => +5.5),
            array('name' => 'Asia/Krasnoyarsk',               'offset' => +8),
            array('name' => 'Asia/Kuala_Lumpur',              'offset' => +8),
            array('name' => 'Asia/Kuwait',                    'offset' => +3),
            array('name' => 'Asia/Kuching',                   'offset' => +8),
            array('name' => 'Asia/Magadan',                   'offset' => +12),
            array('name' => 'Asia/Makassar',                  'offset' => +8),
            array('name' => 'Asia/Manila',                    'offset' => +8),
            array('name' => 'Asia/Muscat',                    'offset' => +4),
            array('name' => 'Asia/Nicosia',                   'offset' => +3),
            array('name' => 'Asia/Novokuznetsk',              'offset' => +7),
            array('name' => 'Asia/Novosibirsk',               'offset' => +7),
            array('name' => 'Asia/Omsk',                      'offset' => +7),
            array('name' => 'Asia/Phnom_Penh',                'offset' => +7),
            array('name' => 'Asia/Pontianak',                 'offset' => +7),
            array('name' => 'Asia/Pyongyang',                 'offset' => +9),
            array('name' => 'Asia/Rangoon',                   'offset' => +6.5),
            array('name' => 'Asia/Samarkand',                 'offset' => +5),
            array('name' => 'Asia/Sakhalin',                  'offset' => +11),
            array('name' => 'Asia/Yekaterinburg',             'offset' => +6),
            array('name' => 'Asia/Seoul',                     'offset' => +9),
            array('name' => 'Asia/Singapore',                 'offset' => +8),
            array('name' => 'Asia/Taipei',                    'offset' => +8),
            array('name' => 'Asia/Tashkent',                  'offset' => +5),
            array('name' => 'Asia/Tbilisi',                   'offset' => +4),
            array('name' => 'Asia/Tehran',                    'offset' => +3.5), // GMT = 3.5, 'Tehran'
            array('name' => 'Asia/Tehran',                    'offset' => +4.5),
            array('name' => 'Asia/Tokyo',                     'offset' => +9),
            array('name' => 'Asia/Thimphu',                   'offset' => +6),
            array('name' => 'Asia/Ulaanbaatar',               'offset' => +8),
            array('name' => 'Asia/Urumqi',                    'offset' => +8),
            array('name' => 'Asia/Oral',                      'offset' => +5),
            array('name' => 'Asia/Harbin',                    'offset' => +8),
            array('name' => 'Asia/Chongqing',                 'offset' => +8),
            array('name' => 'Asia/Shanghai',                  'offset' => +8),
            array('name' => 'Asia/Riyadh',                    'offset' => +3),
            array('name' => 'Asia/Yakutsk',                   'offset' => +10),
            array('name' => 'America/Anguilla',               'offset' => -4),
            array('name' => 'America/Araguaina',              'offset' => -3),
            array('name' => 'America/Argentina/La_Rioja',     'offset' => -3),
            array('name' => 'America/Aruba',                  'offset' => -4),
            array('name' => 'America/Atikokan',               'offset' => -5),
            array('name' => 'America/Bahia_Banderas',         'offset' => -5),
            array('name' => 'America/Blanc-Sablon',           'offset' => -4),
            array('name' => 'America/Cambridge_Bay',          'offset' => -6),
            array('name' => 'America/Curacao',                'offset' => -4),
            array('name' => 'America/Danmarkshavn',           'offset' => 0),
            array('name' => 'America/Dawson',                 'offset' => -7),
            array('name' => 'America/Dawson_Creek',           'offset' => -7),
            array('name' => 'America/Dominica',               'offset' => -4),
            array('name' => 'America/Eirunepe',               'offset' => -4),
            array('name' => 'America/Glace_Bay',              'offset' => -3),
            array('name' => 'America/Godthab',                'offset' => -2),
            array('name' => 'America/Goose_Bay',              'offset' => -3),
            array('name' => 'America/Grand_Turk',             'offset' => -4),
            array('name' => 'America/Inuvik',                 'offset' => -6),
            array('name' => 'America/Iqaluit',                'offset' => -4),
            array('name' => 'America/Menominee',              'offset' => -5),
            array('name' => 'America/Miquelon',               'offset' => -2),
            array('name' => 'America/Montserrat',             'offset' => -4),
            array('name' => 'America/Nipigon',                'offset' => -4),
            array('name' => 'America/Noronha',                'offset' => -2),
            array('name' => 'America/North_Dakota/New_Salem', 'offset' => -5),
            array('name' => 'America/North_Dakota/Center',    'offset' => -5),
            array('name' => 'America/Ojinaga',                'offset' => -6),
            array('name' => 'America/Pangnirtung',            'offset' => -4),
            array('name' => 'America/Port_of_Spain',          'offset' => -4),
            array('name' => 'America/Puerto_Rico',            'offset' => -4),
            array('name' => 'America/Rainy_River',            'offset' => -5),
            array('name' => 'America/Rankin_Inlet',           'offset' => -5),
            array('name' => 'America/Santa_Isabel',           'offset' => -7),
            array('name' => 'America/Scoresbysund',           'offset' => 0),
            array('name' => 'America/Shiprock',               'offset' => -6),
            array('name' => 'America/Swift_Current',          'offset' => -6),
            array('name' => 'America/Tortola',                'offset' => -4),
            array('name' => 'America/Yakutat',                'offset' => -8),
            array('name' => 'America/Adak',                   'offset' => -9),
            array('name' => 'America/Anchorage',              'offset' => -8),
            array('name' => 'America/Antigua',                'offset' => -4),
            array('name' => 'America/Argentina/Catamarca',    'offset' => -3),
            array('name' => 'America/Argentina/Jujuy',        'offset' => -3),
            array('name' => 'America/Argentina/Rio_Gallegos', 'offset' => -3),
            array('name' => 'America/Argentina/Tucuman',      'offset' => -3),
            array('name' => 'America/Argentina/Buenos_Aires', 'offset' => -3),
            array('name' => 'America/Argentina/Cordoba',      'offset' => -3),
            array('name' => 'America/Argentina/Mendoza',      'offset' => -3),
            array('name' => 'America/Argentina/Salta',        'offset' => -3),
            array('name' => 'America/Argentina/San_Juan',     'offset' => -3),
            array('name' => 'America/Argentina/San_Luis',     'offset' => -3),
            array('name' => 'America/Argentina/Ushuaia',      'offset' => -3),
            array('name' => 'America/Asuncion',               'offset' => -4),
            array('name' => 'America/Bahia',                  'offset' => -3),
            array('name' => 'America/Barbados',               'offset' => -4),
            array('name' => 'America/Belem',                  'offset' => -3),
            array('name' => 'America/Belize',                 'offset' => -6),
            array('name' => 'America/Boa_Vista',              'offset' => -4),
            array('name' => 'America/Bogota',                 'offset' => -5),
            array('name' => 'America/Boise',                  'offset' => -6),
            array('name' => 'America/Vancouver',              'offset' => -7),
            array('name' => 'America/Winnipeg',               'offset' => -5),
            array('name' => 'America/Havana',                 'offset' => -4),
            array('name' => 'America/Guyana',                 'offset' => -4),
            array('name' => 'America/Halifax',                'offset' => -3),
            array('name' => 'America/Guadeloupe',             'offset' => -4),
            array('name' => 'America/Guatemala',              'offset' => -6),
            array('name' => 'America/Grenada',                'offset' => -4),
            array('name' => 'America/Guayaquil',              'offset' => -5),
            array('name' => 'America/Denver',                 'offset' => -6),
            array('name' => 'America/Detroit',                'offset' => -4),
            array('name' => 'America/Juneau',                 'offset' => -8),
            array('name' => 'America/Indiana/Knox',           'offset' => -5),
            array('name' => 'America/Indiana/Marengo',        'offset' => -4),
            array('name' => 'America/Indiana/Tell_City',      'offset' => -5),
            array('name' => 'America/Indiana/Vevay',          'offset' => -4),
            array('name' => 'America/Indiana/Winamac',        'offset' => -4),
            array('name' => 'America/Indiana/Vincennes',      'offset' => -4),
            array('name' => 'America/Indiana/Indianapolis',   'offset' => -4),
            array('name' => 'America/Indiana/Petersburg',     'offset' => -4),
            array('name' => 'America/Cayenne',                'offset' => -3),
            array('name' => 'America/Campo_Grande',           'offset' => -4),
            array('name' => 'America/Cancun',                 'offset' => -5),
            array('name' => 'America/Caracas',                'offset' => -4.5),
            array('name' => 'America/Cayman',                 'offset' => -5),
            array('name' => 'America/Kentucky/Monticello',    'offset' => -4),
            array('name' => 'America/Kentucky/Louisville',    'offset' => -4),
            array('name' => 'America/Costa_Rica',             'offset' => -6),
            array('name' => 'America/Cuiaba',                 'offset' => -4),
            array('name' => 'America/Lima',                   'offset' => -5),
            array('name' => 'America/Los_Angeles',            'offset' => -7),
            array('name' => 'America/Managua',                'offset' => -6),
            array('name' => 'America/Manaus',                 'offset' => -4),
            array('name' => 'America/Marigot',                'offset' => -4),
            array('name' => 'America/Martinique',             'offset' => -4),
            array('name' => 'America/Mazatlan',               'offset' => -6),
            array('name' => 'America/Maceio',                 'offset' => -3),
            array('name' => 'America/Matamoros',              'offset' => -5),
            array('name' => 'America/Merida',                 'offset' => -5),
            array('name' => 'America/Mexico_City',            'offset' => -5),
            array('name' => 'America/Moncton',                'offset' => -3),
            array('name' => 'America/Montreal',               'offset' => -4),
            array('name' => 'America/Montevideo',             'offset' => -3),
            array('name' => 'America/Monterrey',              'offset' => -5),
            array('name' => 'America/Nassau',                 'offset' => -4),
            array('name' => 'America/Nome',                   'offset' => -8),
            array('name' => 'America/New_York',               'offset' => -4),
            array('name' => 'America/Panama',                 'offset' => -5),
            array('name' => 'America/Paramaribo',             'offset' => -3),
            array('name' => 'America/Port-au-Prince',         'offset' => -5),
            array('name' => 'America/Porto_Velho',            'offset' => -4),
            array('name' => 'America/Regina',                 'offset' => -6),
            array('name' => 'America/Resolute',               'offset' => -5),
            array('name' => 'America/Recife',                 'offset' => -3),
            array('name' => 'America/Rio_Branco',             'offset' => -4),
            array('name' => 'America/Sao_Paulo',              'offset' => -3),
            array('name' => 'America/Santarem',               'offset' => -3),
            array('name' => 'America/Santo_Domingo',          'offset' => -4),
            array('name' => 'America/Santiago',               'offset' => -4),
            array('name' => 'America/Thunder_Bay',            'offset' => -4),
            array('name' => 'America/Tegucigalpa',            'offset' => -6),
            array('name' => 'America/Tijuana',                'offset' => -7),
            array('name' => 'America/Toronto',                'offset' => -4),
            array('name' => 'America/Thule',                  'offset' => -3),
            array('name' => 'America/Whitehorse',             'offset' => -7),
            array('name' => 'America/Phoenix',                'offset' => -7),
            array('name' => 'America/Fortaleza',              'offset' => -3),
            array('name' => 'America/Chicago',                'offset' => -5),
            array('name' => 'America/Chihuahua',              'offset' => -6),
            array('name' => 'America/Edmonton',               'offset' => -6),
            array('name' => 'America/Yellowknife',            'offset' => -6),
            array('name' => 'America/Hermosillo',             'offset' => -7),
            array('name' => 'America/Jamaica',                'offset' => -5),
            array('name' => 'Antarctica/Casey',               'offset' => +8),
            array('name' => 'Antarctica/DumontDUrville',      'offset' => +10),
            array('name' => 'Antarctica/Macquarie',           'offset' => +11),
            array('name' => 'Antarctica/Palmer',              'offset' => -4),
            array('name' => 'Antarctica/Rothera',             'offset' => -3),
            array('name' => 'Antarctica/South_Pole',          'offset' => +12),
            array('name' => 'Antarctica/Syowa',               'offset' => +3),
            array('name' => 'Antarctica/Vostok',              'offset' => +6),
            array('name' => 'Antarctica/Davis',               'offset' => +7),
            array('name' => 'Antarctica/McMurdo',             'offset' => +12),
            array('name' => 'Antarctica/Mawson',              'offset' => +5),
            array('name' => 'Arctic/Longyearbyen',            'offset' => +2),
            array('name' => 'Atlantic/Cape_Verde',            'offset' => -1),
            array('name' => 'Atlantic/Faroe',                 'offset' => +1),
            array('name' => 'Atlantic/South_Georgia',         'offset' => -2),
            array('name' => 'Atlantic/Azores',                'offset' => 0),
            array('name' => 'Atlantic/Bermuda',               'offset' => -3),
            array('name' => 'Atlantic/Canary',                'offset' => +1),
            array('name' => 'Atlantic/Madeira',               'offset' => +1),
            array('name' => 'Atlantic/Reykjavik',             'offset' => 0),
            array('name' => 'Atlantic/Stanley',               'offset' => -3),
            array('name' => 'Africa/Dar_es_Salaam',           'offset' => +3),
            array('name' => 'Africa/Douala',                  'offset' => +1),
            array('name' => 'Africa/Lubumbashi',              'offset' => +2),
            array('name' => 'Africa/Ndjamena',                'offset' => +1),
            array('name' => 'Africa/Abidjan',                 'offset' => 0),
            array('name' => 'Africa/Addis_Ababa',             'offset' => +3),
            array('name' => 'Africa/Accra',                   'offset' => 0),
            array('name' => 'Africa/Algiers',                 'offset' => +1),
            array('name' => 'Africa/Asmara',                  'offset' => +3),
            array('name' => 'Africa/Bamako',                  'offset' => 0),
            array('name' => 'Africa/Bangui',                  'offset' => +1),
            array('name' => 'Africa/Banjul',                  'offset' => 0),
            array('name' => 'Africa/Bissau',                  'offset' => 0),
            array('name' => 'Africa/Blantyre',                'offset' => +2),
            array('name' => 'Africa/Brazzaville',             'offset' => +1),
            array('name' => 'Africa/Bujumbura',               'offset' => +2),
            array('name' => 'Africa/Windhoek',                'offset' => +1),
            array('name' => 'Africa/Gaborone',                'offset' => +2),
            array('name' => 'Africa/Dakar',                   'offset' => 0),
            array('name' => 'Africa/Djibouti',                'offset' => +3),
            array('name' => 'Africa/Johannesburg',            'offset' => +2),
            array('name' => 'Africa/Cairo',                   'offset' => +2),
            array('name' => 'Africa/Kampala',                 'offset' => +3),
            array('name' => 'Africa/Casablanca',              'offset' => +1),
            array('name' => 'Africa/Kigali',                  'offset' => +2),
            array('name' => 'Africa/Kinshasa',                'offset' => +1),
            array('name' => 'Africa/Conakry',                 'offset' => 0),
            array('name' => 'Africa/Lagos',                   'offset' => +1),
            array('name' => 'Africa/Libreville',              'offset' => +1),
            array('name' => 'Africa/Lome',                    'offset' => 0),
            array('name' => 'Africa/Luanda',                  'offset' => +1),
            array('name' => 'Africa/Lusaka',                  'offset' => +2),
            array('name' => 'Africa/Malabo',                  'offset' => +1),
            array('name' => 'Africa/Maputo',                  'offset' => +2),
            array('name' => 'Africa/Maseru',                  'offset' => +2),
            array('name' => 'Africa/Mbabane',                 'offset' => +2),
            array('name' => 'Africa/Mogadishu',               'offset' => +3),
            array('name' => 'Africa/Monrovia',                'offset' => 0),
            array('name' => 'Africa/Nairobi',                 'offset' => +3),
            array('name' => 'Africa/Niamey',                  'offset' => +1),
            array('name' => 'Africa/Nouakchott',              'offset' => 0),
            array('name' => 'Africa/Porto-Novo	WAT',          'offset' => +1),
            array('name' => 'Africa/Sao_Tome',                'offset' => 0),
            array('name' => 'Africa/Ceuta',                   'offset' => +2),
            array('name' => 'Africa/Tripoli',                 'offset' => +2),
            array('name' => 'Africa/Tunis',                   'offset' => +1),
            array('name' => 'Africa/Ouagadougou',             'offset' => 0),
            array('name' => 'Africa/Freetown',                'offset' => 0),
            array('name' => 'Africa/Harare',                  'offset' => +2),
            array('name' => 'Africa/Khartoum',                'offset' => +3),
            array('name' => 'Europe/Guernsey',                'offset' => +1),
            array('name' => 'Europe/Isle_of_Man',             'offset' => +1),
            array('name' => 'Europe/Tallinn',                 'offset' => +3),
            array('name' => 'Europe/Tirane',                  'offset' => +2),
            array('name' => 'Europe/Uzhgorod',                'offset' => +3),
            array('name' => 'Europe/Zaporozhye',              'offset' => +3),
            array('name' => 'Europe/Amsterdam',               'offset' => +2),
            array('name' => 'Europe/Andorra',                 'offset' => +2),
            array('name' => 'Europe/Athens',                  'offset' => +3),
            array('name' => 'Europe/Belgrade',                'offset' => +2),
            array('name' => 'Europe/Berlin',                  'offset' => +2),
            array('name' => 'Europe/Bratislava',              'offset' => +2),
            array('name' => 'Europe/Brussels',                'offset' => +2),
            array('name' => 'Europe/Budapest',                'offset' => +2),
            array('name' => 'Europe/Bucharest',               'offset' => +3),
            array('name' => 'Europe/Vaduz',                   'offset' => +2),
            array('name' => 'Europe/Warsaw',                  'offset' => +2),
            array('name' => 'Europe/Vatican',                 'offset' => +2),
            array('name' => 'Europe/Vienna',                  'offset' => +2),
            array('name' => 'Europe/Vilnius',                 'offset' => +3),
            array('name' => 'Europe/Volgograd',               'offset' => +4),
            array('name' => 'Europe/Gibraltar',               'offset' => +2),
            array('name' => 'Europe/Jersey',                  'offset' => +1),
            array('name' => 'Europe/Dublin',                  'offset' => +1),
            array('name' => 'Europe/Zagreb',                  'offset' => +2),
            array('name' => 'Europe/Kaliningrad',             'offset' => +3),
            array('name' => 'Europe/Kiev',                    'offset' => +3),
            array('name' => 'Europe/Chisinau',                'offset' => +3),
            array('name' => 'Europe/Copenhagen',              'offset' => +2),
            array('name' => 'Europe/Lisbon',                  'offset' => +1),
            array('name' => 'Europe/London',                  'offset' => +1),
            array('name' => 'Europe/Luxembourg',              'offset' => +2),
            array('name' => 'Europe/Ljubljana',               'offset' => +2),
            array('name' => 'Europe/Madrid',                  'offset' => +2),
            array('name' => 'Europe/Malta',                   'offset' => +2),
            array('name' => 'Europe/Mariehamn',               'offset' => +3),
            array('name' => 'Europe/Minsk',                   'offset' => +3),
            array('name' => 'Europe/Monaco',                  'offset' => +2),
            array('name' => 'Europe/Moscow',                  'offset' => +4),
            array('name' => 'Europe/Oslo',                    'offset' => +2),
            array('name' => 'Europe/Paris',                   'offset' => +2),
            array('name' => 'Europe/Podgorica',               'offset' => +2),
            array('name' => 'Europe/Prague',                  'offset' => +2),
            array('name' => 'Europe/Riga',                    'offset' => +3),
            array('name' => 'Europe/Rome',                    'offset' => +2),
            array('name' => 'Europe/Samara',                  'offset' => +4),
            array('name' => 'Europe/San_Marino',              'offset' => +2),
            array('name' => 'Europe/Sarajevo',                'offset' => +2),
            array('name' => 'Europe/Simferopol',              'offset' => +3),
            array('name' => 'Europe/Skopje',                  'offset' => +2),
            array('name' => 'Europe/Sofia',                   'offset' => +3),
            array('name' => 'Europe/Istanbul',                'offset' => +3),
            array('name' => 'Europe/Stockholm',               'offset' => +2),
            array('name' => 'Europe/Helsinki',                'offset' => +3),
            array('name' => 'Europe/Zurich',                  'offset' => +2),
            array('name' => 'Indian/Chagos',                  'offset' => +6),
            array('name' => 'Indian/Cocos',                   'offset' => +6.5),
            array('name' => 'Indian/Comoro',                  'offset' => +3),
            array('name' => 'Indian/Kerguelen',               'offset' => +5),
            array('name' => 'Indian/Mayotte',                 'offset' => +3),
            array('name' => 'Indian/Antananarivo',            'offset' => +3),
            array('name' => 'Indian/Christmas',               'offset' => +7),
            array('name' => 'Indian/Mauritius',               'offset' => +4),
            array('name' => 'Indian/Maldives',                'offset' => +5),
            array('name' => 'Indian/Mahe',                    'offset' => +4),
            array('name' => 'Indian/Reunion',                 'offset' => +4),
            array('name' => 'Pacific/Efate',                  'offset' => +11),
            array('name' => 'Pacific/Enderbury',              'offset' => +13),
            array('name' => 'Pacific/Fakaofo',                'offset' => +13),
            array('name' => 'Pacific/Funafuti',               'offset' => +12),
            array('name' => 'Pacific/Gambier',                'offset' => -9),
            array('name' => 'Pacific/Johnston',               'offset' => -10),
            array('name' => 'Pacific/Kiritimati',             'offset' => +14),
            array('name' => 'Pacific/Kosrae',                 'offset' => +11),
            array('name' => 'Pacific/Marquesas',              'offset' => -9.5),
            array('name' => 'Pacific/Niue',                   'offset' => -11),
            array('name' => 'Pacific/Palau',                  'offset' => +9),
            array('name' => 'Pacific/Pitcairn',               'offset' => -8),
            array('name' => 'Pacific/Rarotonga',              'offset' => -10),
            array('name' => 'Pacific/Saipan',                 'offset' => +10),
            array('name' => 'Pacific/Tongatapu',              'offset' => +13),
            array('name' => 'Pacific/Wallis',                 'offset' => +12),
            array('name' => 'Pacific/Apia',                   'offset' => +13),
            array('name' => 'Pacific/Galapagos',              'offset' => -6),
            array('name' => 'Pacific/Honolulu',               'offset' => -10),
            array('name' => 'Pacific/Guadalcanal',            'offset' => +11),
            array('name' => 'Pacific/Guam',                   'offset' => +10),
            array('name' => 'Pacific/Kwajalein',              'offset' => +12),
            array('name' => 'Pacific/Kwajalein',              'offset' => -12), // GMT = -12.0, 'Eniwetok, Kwajalein'
            array('name' => 'Pacific/Majuro',                 'offset' => +12),
            array('name' => 'Pacific/Midway',                 'offset' => -11),
            array('name' => 'Pacific/Nauru',                  'offset' => +12),
            array('name' => 'Pacific/Norfolk',                'offset' => +11.5),
            array('name' => 'Pacific/Noumea',                 'offset' => +11),
            array('name' => 'Pacific/Auckland',               'offset' => +12),
            array('name' => 'Pacific/Pago_Pago',              'offset' => -11),
            array('name' => 'Pacific/Easter',                 'offset' => -6),
            array('name' => 'Pacific/Port_Moresby',           'offset' => +10),
            array('name' => 'Pacific/Tahiti',                 'offset' => -10),
            array('name' => 'Pacific/Tarawa',                 'offset' => +12),
            array('name' => 'Pacific/Wake',                   'offset' => +12),
            array('name' => 'Pacific/Fiji',                   'offset' => +12),
            array('name' => 'Pacific/Chatham',                'offset' => +12.75),
        );
    }
}
