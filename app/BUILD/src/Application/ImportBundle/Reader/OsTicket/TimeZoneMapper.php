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
        $timezones = [];
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
        return [
            ['name' => 'Africa/Juba',                    'offset' => +3],
            ['name' => 'America/Creston',                'offset' => -7],
            ['name' => 'America/Kralendijk',             'offset' => -4],
            ['name' => 'America/Lower_Princes',          'offset' => -4],
            ['name' => 'America/Metlakatla',             'offset' => -8],
            ['name' => 'America/North_Dakota/Beulah',    'offset' => -5],
            ['name' => 'America/Sitka',                  'offset' => -8],
            ['name' => 'Asia/Hebron',                    'offset' => +2],
            ['name' => 'Pacific/Chuuk',                  'offset' => +10],
            ['name' => 'Pacific/Pohnpei',                'offset' => +11],
            ['name' => 'Africa/El_Aaiun',                'offset' => 0],
            ['name' => 'America/St_Barthelemy',          'offset' => -4],
            ['name' => 'America/St_Johns',               'offset' => -2.5],
            ['name' => 'America/St_Kitts',               'offset' => -4],
            ['name' => 'America/St_Lucia',               'offset' => -4],
            ['name' => 'America/St_Thomas',              'offset' => -4],
            ['name' => 'America/St_Vincent',             'offset' => -4],
            ['name' => 'America/La_Paz',                 'offset' => -4],
            ['name' => 'America/La_Paz',                 'offset' => -3.5], // GMT = -3.5, 'Newfoundland'
            ['name' => 'America/El_Salvador',            'offset' => -6],
            ['name' => 'Asia/Ho_Chi_Minh',               'offset' => +7],
            ['name' => 'Atlantic/St_Helena',             'offset' => 0],
            ['name' => 'Australia/Currie',               'offset' => +10],
            ['name' => 'Australia/Eucla',                'offset' => +8.75],
            ['name' => 'Australia/Lindeman',             'offset' => +10],
            ['name' => 'Australia/Lord_Howe',            'offset' => +10.5],
            ['name' => 'Australia/Adelaide',             'offset' => +9.5],
            ['name' => 'Australia/Brisbane',             'offset' => +10],
            ['name' => 'Australia/Broken_Hill',          'offset' => +9.5],
            ['name' => 'Australia/Darwin',               'offset' => +9.5],
            ['name' => 'Australia/Melbourne',            'offset' => +10],
            ['name' => 'Australia/Perth',                'offset' => +8],
            ['name' => 'Australia/Sydney',               'offset' => +10],
            ['name' => 'Australia/Hobart',               'offset' => +10],
            ['name' => 'Asia/Aqtobe',                    'offset' => +5],
            ['name' => 'Asia/Anadyr',                    'offset' => +12],
            ['name' => 'Asia/Choibalsan',                'offset' => +8],
            ['name' => 'Asia/Kashgar',                   'offset' => +8],
            ['name' => 'Asia/Kolkata',                   'offset' => +5.5],
            ['name' => 'Asia/Macau',                     'offset' => +8],
            ['name' => 'Asia/Qyzylorda',                 'offset' => +6],
            ['name' => 'Asia/Aden',                      'offset' => +3],
            ['name' => 'Asia/Aqtau',                     'offset' => +5],
            ['name' => 'Asia/Almaty',                    'offset' => +6],
            ['name' => 'Asia/Amman',                     'offset' => +3],
            ['name' => 'Asia/Ashgabat',                  'offset' => +5],
            ['name' => 'Asia/Baghdad',                   'offset' => +3],
            ['name' => 'Asia/Baku',                      'offset' => +5],
            ['name' => 'Asia/Bangkok',                   'offset' => +7],
            ['name' => 'Asia/Bahrain',                   'offset' => +3],
            ['name' => 'Asia/Beirut',                    'offset' => +3],
            ['name' => 'Asia/Bishkek',                   'offset' => +6],
            ['name' => 'Asia/Brunei',                    'offset' => +8],
            ['name' => 'Asia/Vladivostok',               'offset' => +11],
            ['name' => 'Asia/Vientiane',                 'offset' => +7],
            ['name' => 'Asia/Gaza',                      'offset' => +2],
            ['name' => 'Asia/Hong_Kong',                 'offset' => +8],
            ['name' => 'Asia/Dhaka',                     'offset' => +6],
            ['name' => 'Asia/Damascus',                  'offset' => +3],
            ['name' => 'Asia/Jakarta',                   'offset' => +7],
            ['name' => 'Asia/Jayapura',                  'offset' => +9],
            ['name' => 'Asia/Dili',                      'offset' => +9],
            ['name' => 'Asia/Dubai',                     'offset' => +4],
            ['name' => 'Asia/Dushanbe',                  'offset' => +5],
            ['name' => 'Asia/Yerevan',                   'offset' => +4],
            ['name' => 'Asia/Jerusalem',                 'offset' => +3],
            ['name' => 'Asia/Irkutsk',                   'offset' => +9],
            ['name' => 'Asia/Kabul',                     'offset' => +4.5],
            ['name' => 'Asia/Kamchatka',                 'offset' => +12],
            ['name' => 'Asia/Karachi',                   'offset' => +5],
            ['name' => 'Asia/Qatar',                     'offset' => +3],
            ['name' => 'Asia/Kathmandu',                 'offset' => +5.75],
            ['name' => 'Asia/Hovd',                      'offset' => +7],
            ['name' => 'Asia/Colombo',                   'offset' => +5.5],
            ['name' => 'Asia/Krasnoyarsk',               'offset' => +8],
            ['name' => 'Asia/Kuala_Lumpur',              'offset' => +8],
            ['name' => 'Asia/Kuwait',                    'offset' => +3],
            ['name' => 'Asia/Kuching',                   'offset' => +8],
            ['name' => 'Asia/Magadan',                   'offset' => +12],
            ['name' => 'Asia/Makassar',                  'offset' => +8],
            ['name' => 'Asia/Manila',                    'offset' => +8],
            ['name' => 'Asia/Muscat',                    'offset' => +4],
            ['name' => 'Asia/Nicosia',                   'offset' => +3],
            ['name' => 'Asia/Novokuznetsk',              'offset' => +7],
            ['name' => 'Asia/Novosibirsk',               'offset' => +7],
            ['name' => 'Asia/Omsk',                      'offset' => +7],
            ['name' => 'Asia/Phnom_Penh',                'offset' => +7],
            ['name' => 'Asia/Pontianak',                 'offset' => +7],
            ['name' => 'Asia/Pyongyang',                 'offset' => +9],
            ['name' => 'Asia/Rangoon',                   'offset' => +6.5],
            ['name' => 'Asia/Samarkand',                 'offset' => +5],
            ['name' => 'Asia/Sakhalin',                  'offset' => +11],
            ['name' => 'Asia/Yekaterinburg',             'offset' => +6],
            ['name' => 'Asia/Seoul',                     'offset' => +9],
            ['name' => 'Asia/Singapore',                 'offset' => +8],
            ['name' => 'Asia/Taipei',                    'offset' => +8],
            ['name' => 'Asia/Tashkent',                  'offset' => +5],
            ['name' => 'Asia/Tbilisi',                   'offset' => +4],
            ['name' => 'Asia/Tehran',                    'offset' => +3.5], // GMT = 3.5, 'Tehran'
            ['name' => 'Asia/Tehran',                    'offset' => +4.5],
            ['name' => 'Asia/Tokyo',                     'offset' => +9],
            ['name' => 'Asia/Thimphu',                   'offset' => +6],
            ['name' => 'Asia/Ulaanbaatar',               'offset' => +8],
            ['name' => 'Asia/Urumqi',                    'offset' => +8],
            ['name' => 'Asia/Oral',                      'offset' => +5],
            ['name' => 'Asia/Harbin',                    'offset' => +8],
            ['name' => 'Asia/Chongqing',                 'offset' => +8],
            ['name' => 'Asia/Shanghai',                  'offset' => +8],
            ['name' => 'Asia/Riyadh',                    'offset' => +3],
            ['name' => 'Asia/Yakutsk',                   'offset' => +10],
            ['name' => 'America/Anguilla',               'offset' => -4],
            ['name' => 'America/Araguaina',              'offset' => -3],
            ['name' => 'America/Argentina/La_Rioja',     'offset' => -3],
            ['name' => 'America/Aruba',                  'offset' => -4],
            ['name' => 'America/Atikokan',               'offset' => -5],
            ['name' => 'America/Bahia_Banderas',         'offset' => -5],
            ['name' => 'America/Blanc-Sablon',           'offset' => -4],
            ['name' => 'America/Cambridge_Bay',          'offset' => -6],
            ['name' => 'America/Curacao',                'offset' => -4],
            ['name' => 'America/Danmarkshavn',           'offset' => 0],
            ['name' => 'America/Dawson',                 'offset' => -7],
            ['name' => 'America/Dawson_Creek',           'offset' => -7],
            ['name' => 'America/Dominica',               'offset' => -4],
            ['name' => 'America/Eirunepe',               'offset' => -4],
            ['name' => 'America/Glace_Bay',              'offset' => -3],
            ['name' => 'America/Godthab',                'offset' => -2],
            ['name' => 'America/Goose_Bay',              'offset' => -3],
            ['name' => 'America/Grand_Turk',             'offset' => -4],
            ['name' => 'America/Inuvik',                 'offset' => -6],
            ['name' => 'America/Iqaluit',                'offset' => -4],
            ['name' => 'America/Menominee',              'offset' => -5],
            ['name' => 'America/Miquelon',               'offset' => -2],
            ['name' => 'America/Montserrat',             'offset' => -4],
            ['name' => 'America/Nipigon',                'offset' => -4],
            ['name' => 'America/Noronha',                'offset' => -2],
            ['name' => 'America/North_Dakota/New_Salem', 'offset' => -5],
            ['name' => 'America/North_Dakota/Center',    'offset' => -5],
            ['name' => 'America/Ojinaga',                'offset' => -6],
            ['name' => 'America/Pangnirtung',            'offset' => -4],
            ['name' => 'America/Port_of_Spain',          'offset' => -4],
            ['name' => 'America/Puerto_Rico',            'offset' => -4],
            ['name' => 'America/Rainy_River',            'offset' => -5],
            ['name' => 'America/Rankin_Inlet',           'offset' => -5],
            ['name' => 'America/Santa_Isabel',           'offset' => -7],
            ['name' => 'America/Scoresbysund',           'offset' => 0],
            ['name' => 'America/Shiprock',               'offset' => -6],
            ['name' => 'America/Swift_Current',          'offset' => -6],
            ['name' => 'America/Tortola',                'offset' => -4],
            ['name' => 'America/Yakutat',                'offset' => -8],
            ['name' => 'America/Adak',                   'offset' => -9],
            ['name' => 'America/Anchorage',              'offset' => -8],
            ['name' => 'America/Antigua',                'offset' => -4],
            ['name' => 'America/Argentina/Catamarca',    'offset' => -3],
            ['name' => 'America/Argentina/Jujuy',        'offset' => -3],
            ['name' => 'America/Argentina/Rio_Gallegos', 'offset' => -3],
            ['name' => 'America/Argentina/Tucuman',      'offset' => -3],
            ['name' => 'America/Argentina/Buenos_Aires', 'offset' => -3],
            ['name' => 'America/Argentina/Cordoba',      'offset' => -3],
            ['name' => 'America/Argentina/Mendoza',      'offset' => -3],
            ['name' => 'America/Argentina/Salta',        'offset' => -3],
            ['name' => 'America/Argentina/San_Juan',     'offset' => -3],
            ['name' => 'America/Argentina/San_Luis',     'offset' => -3],
            ['name' => 'America/Argentina/Ushuaia',      'offset' => -3],
            ['name' => 'America/Asuncion',               'offset' => -4],
            ['name' => 'America/Bahia',                  'offset' => -3],
            ['name' => 'America/Barbados',               'offset' => -4],
            ['name' => 'America/Belem',                  'offset' => -3],
            ['name' => 'America/Belize',                 'offset' => -6],
            ['name' => 'America/Boa_Vista',              'offset' => -4],
            ['name' => 'America/Bogota',                 'offset' => -5],
            ['name' => 'America/Boise',                  'offset' => -6],
            ['name' => 'America/Vancouver',              'offset' => -7],
            ['name' => 'America/Winnipeg',               'offset' => -5],
            ['name' => 'America/Havana',                 'offset' => -4],
            ['name' => 'America/Guyana',                 'offset' => -4],
            ['name' => 'America/Halifax',                'offset' => -3],
            ['name' => 'America/Guadeloupe',             'offset' => -4],
            ['name' => 'America/Guatemala',              'offset' => -6],
            ['name' => 'America/Grenada',                'offset' => -4],
            ['name' => 'America/Guayaquil',              'offset' => -5],
            ['name' => 'America/Denver',                 'offset' => -6],
            ['name' => 'America/Detroit',                'offset' => -4],
            ['name' => 'America/Juneau',                 'offset' => -8],
            ['name' => 'America/Indiana/Knox',           'offset' => -5],
            ['name' => 'America/Indiana/Marengo',        'offset' => -4],
            ['name' => 'America/Indiana/Tell_City',      'offset' => -5],
            ['name' => 'America/Indiana/Vevay',          'offset' => -4],
            ['name' => 'America/Indiana/Winamac',        'offset' => -4],
            ['name' => 'America/Indiana/Vincennes',      'offset' => -4],
            ['name' => 'America/Indiana/Indianapolis',   'offset' => -4],
            ['name' => 'America/Indiana/Petersburg',     'offset' => -4],
            ['name' => 'America/Cayenne',                'offset' => -3],
            ['name' => 'America/Campo_Grande',           'offset' => -4],
            ['name' => 'America/Cancun',                 'offset' => -5],
            ['name' => 'America/Caracas',                'offset' => -4.5],
            ['name' => 'America/Cayman',                 'offset' => -5],
            ['name' => 'America/Kentucky/Monticello',    'offset' => -4],
            ['name' => 'America/Kentucky/Louisville',    'offset' => -4],
            ['name' => 'America/Costa_Rica',             'offset' => -6],
            ['name' => 'America/Cuiaba',                 'offset' => -4],
            ['name' => 'America/Lima',                   'offset' => -5],
            ['name' => 'America/Los_Angeles',            'offset' => -7],
            ['name' => 'America/Managua',                'offset' => -6],
            ['name' => 'America/Manaus',                 'offset' => -4],
            ['name' => 'America/Marigot',                'offset' => -4],
            ['name' => 'America/Martinique',             'offset' => -4],
            ['name' => 'America/Mazatlan',               'offset' => -6],
            ['name' => 'America/Maceio',                 'offset' => -3],
            ['name' => 'America/Matamoros',              'offset' => -5],
            ['name' => 'America/Merida',                 'offset' => -5],
            ['name' => 'America/Mexico_City',            'offset' => -5],
            ['name' => 'America/Moncton',                'offset' => -3],
            ['name' => 'America/Montreal',               'offset' => -4],
            ['name' => 'America/Montevideo',             'offset' => -3],
            ['name' => 'America/Monterrey',              'offset' => -5],
            ['name' => 'America/Nassau',                 'offset' => -4],
            ['name' => 'America/Nome',                   'offset' => -8],
            ['name' => 'America/New_York',               'offset' => -4],
            ['name' => 'America/Panama',                 'offset' => -5],
            ['name' => 'America/Paramaribo',             'offset' => -3],
            ['name' => 'America/Port-au-Prince',         'offset' => -5],
            ['name' => 'America/Porto_Velho',            'offset' => -4],
            ['name' => 'America/Regina',                 'offset' => -6],
            ['name' => 'America/Resolute',               'offset' => -5],
            ['name' => 'America/Recife',                 'offset' => -3],
            ['name' => 'America/Rio_Branco',             'offset' => -4],
            ['name' => 'America/Sao_Paulo',              'offset' => -3],
            ['name' => 'America/Santarem',               'offset' => -3],
            ['name' => 'America/Santo_Domingo',          'offset' => -4],
            ['name' => 'America/Santiago',               'offset' => -4],
            ['name' => 'America/Thunder_Bay',            'offset' => -4],
            ['name' => 'America/Tegucigalpa',            'offset' => -6],
            ['name' => 'America/Tijuana',                'offset' => -7],
            ['name' => 'America/Toronto',                'offset' => -4],
            ['name' => 'America/Thule',                  'offset' => -3],
            ['name' => 'America/Whitehorse',             'offset' => -7],
            ['name' => 'America/Phoenix',                'offset' => -7],
            ['name' => 'America/Fortaleza',              'offset' => -3],
            ['name' => 'America/Chicago',                'offset' => -5],
            ['name' => 'America/Chihuahua',              'offset' => -6],
            ['name' => 'America/Edmonton',               'offset' => -6],
            ['name' => 'America/Yellowknife',            'offset' => -6],
            ['name' => 'America/Hermosillo',             'offset' => -7],
            ['name' => 'America/Jamaica',                'offset' => -5],
            ['name' => 'Antarctica/Casey',               'offset' => +8],
            ['name' => 'Antarctica/DumontDUrville',      'offset' => +10],
            ['name' => 'Antarctica/Macquarie',           'offset' => +11],
            ['name' => 'Antarctica/Palmer',              'offset' => -4],
            ['name' => 'Antarctica/Rothera',             'offset' => -3],
            ['name' => 'Antarctica/South_Pole',          'offset' => +12],
            ['name' => 'Antarctica/Syowa',               'offset' => +3],
            ['name' => 'Antarctica/Vostok',              'offset' => +6],
            ['name' => 'Antarctica/Davis',               'offset' => +7],
            ['name' => 'Antarctica/McMurdo',             'offset' => +12],
            ['name' => 'Antarctica/Mawson',              'offset' => +5],
            ['name' => 'Arctic/Longyearbyen',            'offset' => +2],
            ['name' => 'Atlantic/Cape_Verde',            'offset' => -1],
            ['name' => 'Atlantic/Faroe',                 'offset' => +1],
            ['name' => 'Atlantic/South_Georgia',         'offset' => -2],
            ['name' => 'Atlantic/Azores',                'offset' => 0],
            ['name' => 'Atlantic/Bermuda',               'offset' => -3],
            ['name' => 'Atlantic/Canary',                'offset' => +1],
            ['name' => 'Atlantic/Madeira',               'offset' => +1],
            ['name' => 'Atlantic/Reykjavik',             'offset' => 0],
            ['name' => 'Atlantic/Stanley',               'offset' => -3],
            ['name' => 'Africa/Dar_es_Salaam',           'offset' => +3],
            ['name' => 'Africa/Douala',                  'offset' => +1],
            ['name' => 'Africa/Lubumbashi',              'offset' => +2],
            ['name' => 'Africa/Ndjamena',                'offset' => +1],
            ['name' => 'Africa/Abidjan',                 'offset' => 0],
            ['name' => 'Africa/Addis_Ababa',             'offset' => +3],
            ['name' => 'Africa/Accra',                   'offset' => 0],
            ['name' => 'Africa/Algiers',                 'offset' => +1],
            ['name' => 'Africa/Asmara',                  'offset' => +3],
            ['name' => 'Africa/Bamako',                  'offset' => 0],
            ['name' => 'Africa/Bangui',                  'offset' => +1],
            ['name' => 'Africa/Banjul',                  'offset' => 0],
            ['name' => 'Africa/Bissau',                  'offset' => 0],
            ['name' => 'Africa/Blantyre',                'offset' => +2],
            ['name' => 'Africa/Brazzaville',             'offset' => +1],
            ['name' => 'Africa/Bujumbura',               'offset' => +2],
            ['name' => 'Africa/Windhoek',                'offset' => +1],
            ['name' => 'Africa/Gaborone',                'offset' => +2],
            ['name' => 'Africa/Dakar',                   'offset' => 0],
            ['name' => 'Africa/Djibouti',                'offset' => +3],
            ['name' => 'Africa/Johannesburg',            'offset' => +2],
            ['name' => 'Africa/Cairo',                   'offset' => +2],
            ['name' => 'Africa/Kampala',                 'offset' => +3],
            ['name' => 'Africa/Casablanca',              'offset' => +1],
            ['name' => 'Africa/Kigali',                  'offset' => +2],
            ['name' => 'Africa/Kinshasa',                'offset' => +1],
            ['name' => 'Africa/Conakry',                 'offset' => 0],
            ['name' => 'Africa/Lagos',                   'offset' => +1],
            ['name' => 'Africa/Libreville',              'offset' => +1],
            ['name' => 'Africa/Lome',                    'offset' => 0],
            ['name' => 'Africa/Luanda',                  'offset' => +1],
            ['name' => 'Africa/Lusaka',                  'offset' => +2],
            ['name' => 'Africa/Malabo',                  'offset' => +1],
            ['name' => 'Africa/Maputo',                  'offset' => +2],
            ['name' => 'Africa/Maseru',                  'offset' => +2],
            ['name' => 'Africa/Mbabane',                 'offset' => +2],
            ['name' => 'Africa/Mogadishu',               'offset' => +3],
            ['name' => 'Africa/Monrovia',                'offset' => 0],
            ['name' => 'Africa/Nairobi',                 'offset' => +3],
            ['name' => 'Africa/Niamey',                  'offset' => +1],
            ['name' => 'Africa/Nouakchott',              'offset' => 0],
            ['name' => 'Africa/Porto-Novo	WAT',          'offset' => +1],
            ['name' => 'Africa/Sao_Tome',                'offset' => 0],
            ['name' => 'Africa/Ceuta',                   'offset' => +2],
            ['name' => 'Africa/Tripoli',                 'offset' => +2],
            ['name' => 'Africa/Tunis',                   'offset' => +1],
            ['name' => 'Africa/Ouagadougou',             'offset' => 0],
            ['name' => 'Africa/Freetown',                'offset' => 0],
            ['name' => 'Africa/Harare',                  'offset' => +2],
            ['name' => 'Africa/Khartoum',                'offset' => +3],
            ['name' => 'Europe/Guernsey',                'offset' => +1],
            ['name' => 'Europe/Isle_of_Man',             'offset' => +1],
            ['name' => 'Europe/Tallinn',                 'offset' => +3],
            ['name' => 'Europe/Tirane',                  'offset' => +2],
            ['name' => 'Europe/Uzhgorod',                'offset' => +3],
            ['name' => 'Europe/Zaporozhye',              'offset' => +3],
            ['name' => 'Europe/Amsterdam',               'offset' => +2],
            ['name' => 'Europe/Andorra',                 'offset' => +2],
            ['name' => 'Europe/Athens',                  'offset' => +3],
            ['name' => 'Europe/Belgrade',                'offset' => +2],
            ['name' => 'Europe/Berlin',                  'offset' => +2],
            ['name' => 'Europe/Bratislava',              'offset' => +2],
            ['name' => 'Europe/Brussels',                'offset' => +2],
            ['name' => 'Europe/Budapest',                'offset' => +2],
            ['name' => 'Europe/Bucharest',               'offset' => +3],
            ['name' => 'Europe/Vaduz',                   'offset' => +2],
            ['name' => 'Europe/Warsaw',                  'offset' => +2],
            ['name' => 'Europe/Vatican',                 'offset' => +2],
            ['name' => 'Europe/Vienna',                  'offset' => +2],
            ['name' => 'Europe/Vilnius',                 'offset' => +3],
            ['name' => 'Europe/Volgograd',               'offset' => +4],
            ['name' => 'Europe/Gibraltar',               'offset' => +2],
            ['name' => 'Europe/Jersey',                  'offset' => +1],
            ['name' => 'Europe/Dublin',                  'offset' => +1],
            ['name' => 'Europe/Zagreb',                  'offset' => +2],
            ['name' => 'Europe/Kaliningrad',             'offset' => +3],
            ['name' => 'Europe/Kiev',                    'offset' => +3],
            ['name' => 'Europe/Chisinau',                'offset' => +3],
            ['name' => 'Europe/Copenhagen',              'offset' => +2],
            ['name' => 'Europe/Lisbon',                  'offset' => +1],
            ['name' => 'Europe/London',                  'offset' => +1],
            ['name' => 'Europe/Luxembourg',              'offset' => +2],
            ['name' => 'Europe/Ljubljana',               'offset' => +2],
            ['name' => 'Europe/Madrid',                  'offset' => +2],
            ['name' => 'Europe/Malta',                   'offset' => +2],
            ['name' => 'Europe/Mariehamn',               'offset' => +3],
            ['name' => 'Europe/Minsk',                   'offset' => +3],
            ['name' => 'Europe/Monaco',                  'offset' => +2],
            ['name' => 'Europe/Moscow',                  'offset' => +4],
            ['name' => 'Europe/Oslo',                    'offset' => +2],
            ['name' => 'Europe/Paris',                   'offset' => +2],
            ['name' => 'Europe/Podgorica',               'offset' => +2],
            ['name' => 'Europe/Prague',                  'offset' => +2],
            ['name' => 'Europe/Riga',                    'offset' => +3],
            ['name' => 'Europe/Rome',                    'offset' => +2],
            ['name' => 'Europe/Samara',                  'offset' => +4],
            ['name' => 'Europe/San_Marino',              'offset' => +2],
            ['name' => 'Europe/Sarajevo',                'offset' => +2],
            ['name' => 'Europe/Simferopol',              'offset' => +3],
            ['name' => 'Europe/Skopje',                  'offset' => +2],
            ['name' => 'Europe/Sofia',                   'offset' => +3],
            ['name' => 'Europe/Istanbul',                'offset' => +3],
            ['name' => 'Europe/Stockholm',               'offset' => +2],
            ['name' => 'Europe/Helsinki',                'offset' => +3],
            ['name' => 'Europe/Zurich',                  'offset' => +2],
            ['name' => 'Indian/Chagos',                  'offset' => +6],
            ['name' => 'Indian/Cocos',                   'offset' => +6.5],
            ['name' => 'Indian/Comoro',                  'offset' => +3],
            ['name' => 'Indian/Kerguelen',               'offset' => +5],
            ['name' => 'Indian/Mayotte',                 'offset' => +3],
            ['name' => 'Indian/Antananarivo',            'offset' => +3],
            ['name' => 'Indian/Christmas',               'offset' => +7],
            ['name' => 'Indian/Mauritius',               'offset' => +4],
            ['name' => 'Indian/Maldives',                'offset' => +5],
            ['name' => 'Indian/Mahe',                    'offset' => +4],
            ['name' => 'Indian/Reunion',                 'offset' => +4],
            ['name' => 'Pacific/Efate',                  'offset' => +11],
            ['name' => 'Pacific/Enderbury',              'offset' => +13],
            ['name' => 'Pacific/Fakaofo',                'offset' => +13],
            ['name' => 'Pacific/Funafuti',               'offset' => +12],
            ['name' => 'Pacific/Gambier',                'offset' => -9],
            ['name' => 'Pacific/Johnston',               'offset' => -10],
            ['name' => 'Pacific/Kiritimati',             'offset' => +14],
            ['name' => 'Pacific/Kosrae',                 'offset' => +11],
            ['name' => 'Pacific/Marquesas',              'offset' => -9.5],
            ['name' => 'Pacific/Niue',                   'offset' => -11],
            ['name' => 'Pacific/Palau',                  'offset' => +9],
            ['name' => 'Pacific/Pitcairn',               'offset' => -8],
            ['name' => 'Pacific/Rarotonga',              'offset' => -10],
            ['name' => 'Pacific/Saipan',                 'offset' => +10],
            ['name' => 'Pacific/Tongatapu',              'offset' => +13],
            ['name' => 'Pacific/Wallis',                 'offset' => +12],
            ['name' => 'Pacific/Apia',                   'offset' => +13],
            ['name' => 'Pacific/Galapagos',              'offset' => -6],
            ['name' => 'Pacific/Honolulu',               'offset' => -10],
            ['name' => 'Pacific/Guadalcanal',            'offset' => +11],
            ['name' => 'Pacific/Guam',                   'offset' => +10],
            ['name' => 'Pacific/Kwajalein',              'offset' => +12],
            ['name' => 'Pacific/Kwajalein',              'offset' => -12], // GMT = -12.0, 'Eniwetok, Kwajalein'
            ['name' => 'Pacific/Majuro',                 'offset' => +12],
            ['name' => 'Pacific/Midway',                 'offset' => -11],
            ['name' => 'Pacific/Nauru',                  'offset' => +12],
            ['name' => 'Pacific/Norfolk',                'offset' => +11.5],
            ['name' => 'Pacific/Noumea',                 'offset' => +11],
            ['name' => 'Pacific/Auckland',               'offset' => +12],
            ['name' => 'Pacific/Pago_Pago',              'offset' => -11],
            ['name' => 'Pacific/Easter',                 'offset' => -6],
            ['name' => 'Pacific/Port_Moresby',           'offset' => +10],
            ['name' => 'Pacific/Tahiti',                 'offset' => -10],
            ['name' => 'Pacific/Tarawa',                 'offset' => +12],
            ['name' => 'Pacific/Wake',                   'offset' => +12],
            ['name' => 'Pacific/Fiji',                   'offset' => +12],
            ['name' => 'Pacific/Chatham',                'offset' => +12.75],
        ];
    }
}
