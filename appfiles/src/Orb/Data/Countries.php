<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Data
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Data;

use Orb\Util\Arrays;

class Countries
{
	/**
	 * Array of country codes to their names.
	 * @var array
	 */
	protected static $code_to_name = array(
		'AF' => 'Afghanistan',
		'AL' => 'Albania',
		'DZ' => 'Algeria',
		'AS' => 'American Samoa',
		'AD' => 'Andorra',
		'AO' => 'Angola',
		'AI' => 'Anguilla',
		'AQ' => 'Antarctica',
		'AG' => 'Antigua And Barbuda',
		'AR' => 'Argentina',
		'AM' => 'Armenia',
		'AW' => 'Aruba',
		'AU' => 'Australia',
		'AT' => 'Austria',
		'AZ' => 'Azerbaijan',
		'BS' => 'Bahamas',
		'BH' => 'Bahrain',
		'BD' => 'Bangladesh',
		'BB' => 'Barbados',
		'BY' => 'Belarus',
		'BE' => 'Belgium',
		'BZ' => 'Belize',
		'BJ' => 'Benin',
		'BM' => 'Bermuda',
		'BT' => 'Bhutan',
		'BO' => 'Bolivia',
		'BA' => 'Bosnia And Herzegovina',
		'BW' => 'Botswana',
		'BV' => 'Bouvet Island',
		'BR' => 'Brazil',
		'IO' => 'British Indian Ocean Territory',
		'BN' => 'Brunei',
		'BG' => 'Bulgaria',
		'BF' => 'Burkina Faso',
		'BI' => 'Burundi',
		'KH' => 'Cambodia',
		'CM' => 'Cameroon',
		'CA' => 'Canada',
		'CV' => 'Cape Verde',
		'KY' => 'Cayman Islands',
		'CF' => 'Central African Republic',
		'TD' => 'Chad',
		'CL' => 'Chile',
		'CN' => 'China',
		'CX' => 'Christmas Island',
		'CC' => 'Cocos (Keeling) Islands',
		'CO' => 'Columbia',
		'KM' => 'Comoros',
		'CG' => 'Congo',
		'CK' => 'Cook Islands',
		'CR' => 'Costa Rica',
		'CI' => 'Cote D\'Ivorie (Ivory Coast)',
		'HR' => 'Croatia (Hrvatska)',
		'CU' => 'Cuba',
		'CY' => 'Cyprus',
		'CZ' => 'Czech Republic',
		'CD' => 'Democratic Republic Of Congo (Zaire)',
		'DK' => 'Denmark',
		'DJ' => 'Djibouti',
		'DM' => 'Dominica',
		'DO' => 'Dominican Republic',
		'TP' => 'East Timor',
		'EC' => 'Ecuador',
		'EG' => 'Egypt',
		'SV' => 'El Salvador',
		'GQ' => 'Equatorial Guinea',
		'ER' => 'Eritrea',
		'EE' => 'Estonia',
		'ET' => 'Ethiopia',
		'FK' => 'Falkland Islands (Malvinas)',
		'FO' => 'Faroe Islands',
		'FJ' => 'Fiji',
		'FI' => 'Finland',
		'FR' => 'France',
		'FX' => 'France, Metropolitan',
		'GF' => 'French Guinea',
		'PF' => 'French Polynesia',
		'TF' => 'French Southern Territories',
		'GA' => 'Gabon',
		'GM' => 'Gambia',
		'GE' => 'Georgia',
		'DE' => 'Germany',
		'GH' => 'Ghana',
		'GI' => 'Gibraltar',
		'GR' => 'Greece',
		'GL' => 'Greenland',
		'GD' => 'Grenada',
		'GP' => 'Guadeloupe',
		'GU' => 'Guam',
		'GT' => 'Guatemala',
		'GN' => 'Guinea',
		'GW' => 'Guinea-Bissau',
		'GY' => 'Guyana',
		'HT' => 'Haiti',
		'HM' => 'Heard And McDonald Islands',
		'HN' => 'Honduras',
		'HK' => 'Hong Kong',
		'HU' => 'Hungary',
		'IS' => 'Iceland',
		'IN' => 'India',
		'ID' => 'Indonesia',
		'IR' => 'Iran',
		'IQ' => 'Iraq',
		'IE' => 'Ireland',
		'IL' => 'Israel',
		'IT' => 'Italy',
		'JM' => 'Jamaica',
		'JP' => 'Japan',
		'JO' => 'Jordan',
		'KZ' => 'Kazakhstan',
		'KE' => 'Kenya',
		'KI' => 'Kiribati',
		'KW' => 'Kuwait',
		'KG' => 'Kyrgyzstan',
		'LA' => 'Laos',
		'LV' => 'Latvia',
		'LB' => 'Lebanon',
		'LS' => 'Lesotho',
		'LR' => 'Liberia',
		'LY' => 'Libya',
		'LI' => 'Liechtenstein',
		'LT' => 'Lithuania',
		'LU' => 'Luxembourg',
		'MO' => 'Macau',
		'MK' => 'Macedonia',
		'MG' => 'Madagascar',
		'MW' => 'Malawi',
		'MY' => 'Malaysia',
		'MV' => 'Maldives',
		'ML' => 'Mali',
		'MT' => 'Malta',
		'MH' => 'Marshall Islands',
		'MQ' => 'Martinique',
		'MR' => 'Mauritania',
		'MU' => 'Mauritius',
		'YT' => 'Mayotte',
		'MX' => 'Mexico',
		'FM' => 'Micronesia',
		'MD' => 'Moldova',
		'MC' => 'Monaco',
		'MN' => 'Mongolia',
		'MS' => 'Montserrat',
		'MA' => 'Morocco',
		'MZ' => 'Mozambique',
		'MM' => 'Myanmar (Burma)',
		'NA' => 'Namibia',
		'NR' => 'Nauru',
		'NP' => 'Nepal',
		'NL' => 'Netherlands',
		'AN' => 'Netherlands Antilles',
		'NC' => 'New Caledonia',
		'NZ' => 'New Zealand',
		'NI' => 'Nicaragua',
		'NE' => 'Niger',
		'NG' => 'Nigeria',
		'NU' => 'Niue',
		'NF' => 'Norfolk Island',
		'KP' => 'North Korea',
		'MP' => 'Northern Mariana Islands',
		'NO' => 'Norway',
		'OM' => 'Oman',
		'PK' => 'Pakistan',
		'PW' => 'Palau',
		'PA' => 'Panama',
		'PG' => 'Papua New Guinea',
		'PY' => 'Paraguay',
		'PE' => 'Peru',
		'PH' => 'Philippines',
		'PN' => 'Pitcairn',
		'PL' => 'Poland',
		'PT' => 'Portugal',
		'PR' => 'Puerto Rico',
		'QA' => 'Qatar',
		'RE' => 'Reunion',
		'RO' => 'Romania',
		'RU' => 'Russia',
		'RW' => 'Rwanda',
		'SH' => 'Saint Helena',
		'KN' => 'Saint Kitts And Nevis',
		'LC' => 'Saint Lucia',
		'PM' => 'Saint Pierre And Miquelon',
		'VC' => 'Saint Vincent And The Grenadines',
		'SM' => 'San Marino',
		'ST' => 'Sao Tome And Principe',
		'SA' => 'Saudi Arabia',
		'SN' => 'Senegal',
		'SC' => 'Seychelles',
		'SL' => 'Sierra Leone',
		'SG' => 'Singapore',
		'SK' => 'Slovak Republic',
		'SI' => 'Slovenia',
		'SB' => 'Solomon Islands',
		'SO' => 'Somalia',
		'ZA' => 'South Africa',
		'GS' => 'South Georgia And South Sandwich Islands',
		'KR' => 'South Korea',
		'ES' => 'Spain',
		'LK' => 'Sri Lanka',
		'SD' => 'Sudan',
		'SR' => 'Suriname',
		'SJ' => 'Svalbard And Jan Mayen',
		'SZ' => 'Swaziland',
		'SE' => 'Sweden',
		'CH' => 'Switzerland',
		'SY' => 'Syria',
		'TW' => 'Taiwan',
		'TJ' => 'Tajikistan',
		'TZ' => 'Tanzania',
		'TH' => 'Thailand',
		'TG' => 'Togo',
		'TK' => 'Tokelau',
		'TO' => 'Tonga',
		'TT' => 'Trinidad And Tobago',
		'TN' => 'Tunisia',
		'TR' => 'Turkey',
		'TM' => 'Turkmenistan',
		'TC' => 'Turks And Caicos Islands',
		'TV' => 'Tuvalu',
		'UG' => 'Uganda',
		'UA' => 'Ukraine',
		'AE' => 'United Arab Emirates',
		'UK' => 'United Kingdom',
		'US' => 'United States',
		'UM' => 'United States Minor Outlying Islands',
		'UY' => 'Uruguay',
		'UZ' => 'Uzbekistan',
		'VU' => 'Vanuatu',
		'VA' => 'Vatican City (Holy See)',
		'VE' => 'Venezuela',
		'VN' => 'Vietnam',
		'VG' => 'Virgin Islands (British)',
		'VI' => 'Virgin Islands (US)',
		'WF' => 'Wallis And Futuna Islands',
		'EH' => 'Western Sahara',
		'WS' => 'Western Samoa',
		'YE' => 'Yemen',
		'YU' => 'Yugoslavia',
		'ZM' => 'Zambia',
		'ZW' => 'Zimbabwe'
	);

	/**
	 * Maps country code to 3-letter continent code.
	 * @var array
	 */
	protected static $code_to_continent = array(
		'AD' => 'EUR',
		'AE' => 'ASI',
		'AF' => 'ASI',
		'AG' => 'AMS',
		'AI' => 'AMS',
		'AL' => 'EUR',
		'AM' => 'ASI',
		'AN' => 'AMS',
		'AO' => 'AFR',
		'AQ' => 'OCE',
		'AR' => 'AMS',
		'AS' => 'OCE',
		'AT' => 'EUR',
		'AU' => 'OCE',
		'AW' => 'AMS',
		'AX' => 'EUR',
		'AZ' => 'ASI',
		'BA' => 'EUR',
		'BB' => 'AMS',
		'BD' => 'ASI',
		'BE' => 'EUR',
		'BF' => 'AFR',
		'BG' => 'EUR',
		'BH' => 'ASI',
		'BI' => 'AFR',
		'BJ' => 'AFR',
		'BL' => 'AMS',
		'BM' => 'AMS',
		'BN' => 'ASI',
		'BO' => 'AMS',
		'BR' => 'AMS',
		'BS' => 'AMS',
		'BT' => 'ASI',
		'BV' => 'OCE',
		'BW' => 'AFR',
		'BY' => 'EUR',
		'BZ' => 'AMS',
		'CA' => 'AMN',
		'CC' => 'OCE',
		'CD' => 'AFR',
		'CF' => 'AFR',
		'CG' => 'AFR',
		'CH' => 'EUR',
		'CI' => 'AFR',
		'CK' => 'ASI',
		'CL' => 'AMS',
		'CM' => 'AFR',
		'CN' => 'ASI',
		'CO' => 'AMS',
		'CR' => 'AMS',
		'CU' => 'AMS',
		'CV' => 'AFR',
		'CX' => 'OCE',
		'CY' => 'EUR',
		'CZ' => 'EUR',
		'DE' => 'EUR',
		'DJ' => 'AFR',
		'DK' => 'EUR',
		'DM' => 'AMS',
		'DO' => 'AMS',
		'DZ' => 'AFR',
		'EC' => 'AMS',
		'EE' => 'EUR',
		'EG' => 'AFR',
		'EH' => 'AFR',
		'ER' => 'AFR',
		'ES' => 'EUR',
		'ET' => 'AFR',
		'FI' => 'EUR',
		'FJ' => 'OCE',
		'FK' => 'AMS',
		'FM' => 'OCE',
		'FO' => 'EUR',
		'FR' => 'EUR',
		'GA' => 'AFR',
		'GB' => 'EUR',
		'GD' => 'AMS',
		'GE' => 'ASI',
		'GF' => 'AMS',
		'GG' => 'EUR',
		'GH' => 'AFR',
		'GI' => 'AFR',
		'GL' => 'AMN',
		'GM' => 'AFR',
		'GN' => 'AFR',
		'GP' => 'AMS',
		'GQ' => 'AFR',
		'GR' => 'EUR',
		'GS' => 'EUR',
		'GT' => 'AMS',
		'GU' => 'ASI',
		'GW' => 'AFR',
		'GY' => 'AMS',
		'HK' => 'ASI',
		'HM' => 'OCE',
		'HN' => 'AMS',
		'HR' => 'EUR',
		'HT' => 'AMS',
		'HU' => 'EUR',
		'ID' => 'ASI',
		'IE' => 'EUR',
		'IL' => 'ASI',
		'IM' => 'EUR',
		'IN' => 'ASI',
		'IO' => 'ASI',
		'IQ' => 'ASI',
		'IR' => 'ASI',
		'IS' => 'EUR',
		'IT' => 'EUR',
		'JE' => 'EUR',
		'JM' => 'AMS',
		'JO' => 'ASI',
		'JP' => 'ASI',
		'KE' => 'AFR',
		'KG' => 'ASI',
		'KH' => 'ASI',
		'KI' => 'OCE',
		'KM' => 'AFR',
		'KN' => 'AMS',
		'KP' => 'ASI',
		'KR' => 'ASI',
		'KW' => 'ASI',
		'KY' => 'AMS',
		'KZ' => 'ASI',
		'LA' => 'ASI',
		'LB' => 'ASI',
		'LC' => 'AMS',
		'LI' => 'EUR',
		'LK' => 'ASI',
		'LR' => 'AFR',
		'LS' => 'AFR',
		'LT' => 'EUR',
		'LU' => 'EUR',
		'LV' => 'EUR',
		'LY' => 'AFR',
		'MA' => 'AFR',
		'MC' => 'EUR',
		'MD' => 'EUR',
		'ME' => 'EUR',
		'MF' => 'AMS',
		'MG' => 'AFR',
		'MH' => 'OCE',
		'MK' => 'EUR',
		'ML' => 'AFR',
		'MM' => 'ASI',
		'MN' => 'ASI',
		'MO' => 'ASI',
		'MP' => 'ASI',
		'MQ' => 'AMS',
		'MR' => 'AFR',
		'MS' => 'AMS',
		'MT' => 'EUR',
		'MU' => 'AFR',
		'MV' => 'ASI',
		'MW' => 'AFR',
		'MX' => 'AMS',
		'MY' => 'ASI',
		'MZ' => 'AFR',
		'NA' => 'AFR',
		'NC' => 'OCE',
		'NE' => 'AFR',
		'NF' => 'OCE',
		'NG' => 'AFR',
		'NI' => 'AMS',
		'NL' => 'EUR',
		'NO' => 'EUR',
		'NP' => 'ASI',
		'NR' => 'OCE',
		'NU' => 'OCE',
		'NZ' => 'OCE',
		'OM' => 'ASI',
		'PA' => 'AMS',
		'PE' => 'AMS',
		'PF' => 'OCE',
		'PG' => 'OCE',
		'PH' => 'ASI',
		'PK' => 'ASI',
		'PL' => 'EUR',
		'PM' => 'AMN',
		'PN' => 'OCE',
		'PR' => 'AMS',
		'PS' => 'ASI',
		'PT' => 'EUR',
		'PW' => 'OCE',
		'PY' => 'AMS',
		'QA' => 'ASI',
		'RE' => 'AFR',
		'RO' => 'EUR',
		'RS' => 'EUR',
		'RU' => 'EUR',
		'RW' => 'AFR',
		'SA' => 'ASI',
		'SB' => 'OCE',
		'SC' => 'AFR',
		'SD' => 'AFR',
		'SE' => 'EUR',
		'SG' => 'ASI',
		'SH' => 'AFR',
		'SI' => 'EUR',
		'SJ' => 'EUR',
		'SK' => 'EUR',
		'SL' => 'AFR',
		'SM' => 'EUR',
		'SN' => 'AFR',
		'SO' => 'AFR',
		'SR' => 'AMS',
		'ST' => 'AFR',
		'SV' => 'AMS',
		'SY' => 'ASI',
		'SZ' => 'AFR',
		'TC' => 'AMS',
		'TD' => 'AFR',
		'TF' => 'OCE',
		'TG' => 'AFR',
		'TH' => 'ASI',
		'TJ' => 'ASI',
		'TK' => 'OCE',
		'TL' => 'ASI',
		'TM' => 'ASI',
		'TN' => 'AFR',
		'TO' => 'OCE',
		'TR' => 'EUR',
		'TT' => 'AMS',
		'TV' => 'ASI',
		'TW' => 'ASI',
		'TZ' => 'AFR',
		'UA' => 'EUR',
		'UG' => 'AFR',
		'UM' => 'OCE',
		'US' => 'AMN',
		'UY' => 'AMS',
		'UZ' => 'ASI',
		'VA' => 'EUR',
		'VC' => 'AMS',
		'VE' => 'AMS',
		'VG' => 'AMS',
		'VI' => 'AMS',
		'VN' => 'ASI',
		'VU' => 'OCE',
		'WF' => 'OCE',
		'WS' => 'ASI',
		'YE' => 'ASI',
		'YT' => 'AFR',
		'ZA' => 'AFR',
		'ZM' => 'AFR',
		'ZW' => 'AFR',

		// exceptionally reserved
		'AC' => 'AFR', // .ac TLD
		'CP' => 'AMS',
		'DG' => 'ASI',
		'EA' => 'AFR',
		'EU' => 'EUR', // .eu TLD
		'FX' => 'EUR',
		'IC' => 'AFR',
		'SU' => 'EUR', // .su TLD
		'TA' => 'AFR',
		'UK' => 'EUR', // .uk TLD

		// transitionally reserved
		'BU' => 'ASI',
		'CS' => 'EUR', // former Serbia and Montenegro
		'NT' => 'ASI',
		'SF' => 'EUR',
		'TP' => 'OCE', // .tp TLD
		'YU' => 'EUR', // .yu TLD
		'ZR' => 'AFR',
	);

	/**
	 * A simple lookup array to try and reverse a country to name. Not very good.
	 * @var array
	 */
	protected static $name_to_code = array(
		'afghanistan' => 'AF',
		'albania' => 'AL',
		'algeria' => 'DZ',
		'americansamoa' => 'AS',
		'andorra' => 'AD',
		'angola' => 'AO',
		'anguilla' => 'AI',
		'antarctica' => 'AQ',
		'antiguaandbarbuda' => 'AG',
		'argentina' => 'AR',
		'armenia' => 'AM',
		'aruba' => 'AW',
		'australia' => 'AU',
		'austria' => 'AT',
		'azerbaijan' => 'AZ',
		'bahamas' => 'BS',
		'bahrain' => 'BH',
		'bangladesh' => 'BD',
		'barbados' => 'BB',
		'belarus' => 'BY',
		'belgium' => 'BE',
		'belize' => 'BZ',
		'benin' => 'BJ',
		'bermuda' => 'BM',
		'bhutan' => 'BT',
		'bolivia' => 'BO',
		'bosniaandherzegovina' => 'BA',
		'botswana' => 'BW',
		'bouvetisland' => 'BV',
		'brazil' => 'BR',
		'britishindianoceanterritory' => 'IO',
		'brunei' => 'BN',
		'bulgaria' => 'BG',
		'burkinafaso' => 'BF',
		'burundi' => 'BI',
		'cambodia' => 'KH',
		'cameroon' => 'CM',
		'canada' => 'CA',
		'capeverde' => 'CV',
		'caymanislands' => 'KY',
		'centralafricanrepublic' => 'CF',
		'chad' => 'TD',
		'chile' => 'CL',
		'china' => 'CN',
		'christmasisland' => 'CX',
		'cocos(keeling)islands' => 'CC',
		'columbia' => 'CO',
		'comoros' => 'KM',
		'congo' => 'CG',
		'cookislands' => 'CK',
		'costarica' => 'CR',
		'cotedivorieivorycoast' => 'CI',
		'croatiahrvatska' => 'HR',
		'cuba' => 'CU',
		'cyprus' => 'CY',
		'czechrepublic' => 'CZ',
		'democraticrepublicofcongozaire' => 'CD',
		'denmark' => 'DK',
		'djibouti' => 'DJ',
		'dominica' => 'DM',
		'dominicanrepublic' => 'DO',
		'easttimor' => 'TP',
		'ecuador' => 'EC',
		'egypt' => 'EG',
		'elsalvador' => 'SV',
		'equatorialguinea' => 'GQ',
		'eritrea' => 'ER',
		'estonia' => 'EE',
		'ethiopia' => 'ET',
		'falklandislandsmalvinas' => 'FK',
		'faroeislands' => 'FO',
		'fiji' => 'FJ',
		'finland' => 'FI',
		'france' => 'FR',
		'france,metropolitan' => 'FX',
		'frenchguinea' => 'GF',
		'frenchpolynesia' => 'PF',
		'frenchsouthernterritories' => 'TF',
		'gabon' => 'GA',
		'gambia' => 'GM',
		'georgia' => 'GE',
		'germany' => 'DE',
		'ghana' => 'GH',
		'gibraltar' => 'GI',
		'greece' => 'GR',
		'greenland' => 'GL',
		'grenada' => 'GD',
		'guadeloupe' => 'GP',
		'guam' => 'GU',
		'guatemala' => 'GT',
		'guinea' => 'GN',
		'guinea-bissau' => 'GW',
		'guyana' => 'GY',
		'haiti' => 'HT',
		'heardandmcdonaldislands' => 'HM',
		'honduras' => 'HN',
		'hongkong' => 'HK',
		'hungary' => 'HU',
		'iceland' => 'IS',
		'india' => 'IN',
		'indonesia' => 'ID',
		'iran' => 'IR',
		'iraq' => 'IQ',
		'ireland' => 'IE',
		'israel' => 'IL',
		'italy' => 'IT',
		'jamaica' => 'JM',
		'japan' => 'JP',
		'jordan' => 'JO',
		'kazakhstan' => 'KZ',
		'kenya' => 'KE',
		'kiribati' => 'KI',
		'kuwait' => 'KW',
		'kyrgyzstan' => 'KG',
		'laos' => 'LA',
		'latvia' => 'LV',
		'lebanon' => 'LB',
		'lesotho' => 'LS',
		'liberia' => 'LR',
		'libya' => 'LY',
		'liechtenstein' => 'LI',
		'lithuania' => 'LT',
		'luxembourg' => 'LU',
		'macau' => 'MO',
		'macedonia' => 'MK',
		'madagascar' => 'MG',
		'malawi' => 'MW',
		'malaysia' => 'MY',
		'maldives' => 'MV',
		'mali' => 'ML',
		'malta' => 'MT',
		'marshallislands' => 'MH',
		'martinique' => 'MQ',
		'mauritania' => 'MR',
		'mauritius' => 'MU',
		'mayotte' => 'YT',
		'mexico' => 'MX',
		'micronesia' => 'FM',
		'moldova' => 'MD',
		'monaco' => 'MC',
		'mongolia' => 'MN',
		'montserrat' => 'MS',
		'morocco' => 'MA',
		'mozambique' => 'MZ',
		'myanmar(burma)' => 'MM',
		'namibia' => 'NA',
		'nauru' => 'NR',
		'nepal' => 'NP',
		'netherlands' => 'NL',
		'netherlandsantilles' => 'AN',
		'newcaledonia' => 'NC',
		'newzealand' => 'NZ',
		'nicaragua' => 'NI',
		'niger' => 'NE',
		'nigeria' => 'NG',
		'niue' => 'NU',
		'norfolkisland' => 'NF',
		'northkorea' => 'KP',
		'northernmarianaislands' => 'MP',
		'norway' => 'NO',
		'oman' => 'OM',
		'pakistan' => 'PK',
		'palau' => 'PW',
		'panama' => 'PA',
		'papuanewguinea' => 'PG',
		'paraguay' => 'PY',
		'peru' => 'PE',
		'philippines' => 'PH',
		'pitcairn' => 'PN',
		'poland' => 'PL',
		'portugal' => 'PT',
		'puertorico' => 'PR',
		'qatar' => 'QA',
		'reunion' => 'RE',
		'romania' => 'RO',
		'russia' => 'RU',
		'rwanda' => 'RW',
		'sainthelena' => 'SH',
		'saintkittsandnevis' => 'KN',
		'saintlucia' => 'LC',
		'saintpierreandmiquelon' => 'PM',
		'saintvincentandthegrenadines' => 'VC',
		'sanmarino' => 'SM',
		'saotomeandprincipe' => 'ST',
		'saudiarabia' => 'SA',
		'senegal' => 'SN',
		'seychelles' => 'SC',
		'sierraleone' => 'SL',
		'singapore' => 'SG',
		'slovakrepublic' => 'SK',
		'slovenia' => 'SI',
		'solomonislands' => 'SB',
		'somalia' => 'SO',
		'southafrica' => 'ZA',
		'southgeorgiaandsouthsandwichislands' => 'GS',
		'southkorea' => 'KR',
		'spain' => 'ES',
		'srilanka' => 'LK',
		'sudan' => 'SD',
		'suriname' => 'SR',
		'svalbardandjanmayen' => 'SJ',
		'swaziland' => 'SZ',
		'sweden' => 'SE',
		'switzerland' => 'CH',
		'syria' => 'SY',
		'taiwan' => 'TW',
		'tajikistan' => 'TJ',
		'tanzania' => 'TZ',
		'thailand' => 'TH',
		'togo' => 'TG',
		'tokelau' => 'TK',
		'tonga' => 'TO',
		'trinidadandtobago' => 'TT',
		'tunisia' => 'TN',
		'turkey' => 'TR',
		'turkmenistan' => 'TM',
		'turksandcaicosislands' => 'TC',
		'tuvalu' => 'TV',
		'uganda' => 'UG',
		'ukraine' => 'UA',
		'unitedarabemirates' => 'AE',
		'unitedkingdom' => 'UK',
		'unitedstates' => 'US',
		'unitedstatesminoroutlyingislands' => 'UM',
		'uruguay' => 'UY',
		'uzbekistan' => 'UZ',
		'vanuatu' => 'VU',
		'vaticancityholysee' => 'VA',
		'venezuela' => 'VE',
		'vietnam' => 'VN',
		'virginislandsbritish' => 'VG',
		'virginislandsus' => 'VI',
		'wallisandfutunaislands' => 'WF',
		'westernsahara' => 'EH',
		'westernsamoa' => 'WS',
		'yemen' => 'YE',
		'yugoslavia' => 'YU',
		'zambia' => 'ZM',
		'zimbabwe' => 'ZW'
	);



	/**
	 * Check if a country code is in the list.
	 *
	 * @param  string  $code  The code to check
	 * @return bool
	 */
	public static function isCountry($code)
	{
		return isset(self::$code_to_name[$code]);
	}


	/**
	 * Get an array of simple country codes.
	 *
	 * @return array
	 */
	public static function getCountryCodes()
	{
		return array_keys(self::$code_to_name);
	}


	/**
	 * Get an array of code=>name of countries.
	 *
	 * @return array
	 */
	public static function getCountryArray()
	{
		return self::$code_to_name;
	}


	/*
	 * Get an array of country names
	 *
	 * @return string[]
	 */
	public static function getCountryNames()
	{
		return array_keys(self::$code_to_name);
	}


	/**
	 * Get an array of code=>continent of countries.
	 *
	 * @return array
	 */
	public static function getContientArray()
	{
		return self::$code_to_continent;
	}


	/**
	 * Ge the 3-letter continent code for a specified country.
	 *
	 * @param   string  $code  The two letter country code
	 * @return  string
	 */
	public static function getContinentFromCode($code)
	{
		if (!isset(self::$code_to_continent[$code])) {
			return null;
		}

		return self::$code_to_continent[$code];
	}


	/**
	 * Get the country name from a code. Returns null if no country exists.
	 *
	 * @param   string  $code  The two letter country code
	 * @return  string
	 */
	public static function getCountryFromCode($code)
	{
		if (!isset(self::$code_to_name[$code])) {
			return null;
		}

		return self::$code_to_name[$code];
	}


	/**
	 * Tries to get a country code from a country name. This isn't very
	 * reliable since some countries can have different ways of expressing
	 * their name.
	 *
	 * Returns false when a country couldnt be found.
	 *
	 * @param   string  $country  The country name
	 * @return  string
	 */
	public static function getCodeFromCountry($country)
	{
		$country = preg_replace('#[^a-z]#', '', strtolower($country));

		if (!isset(self::$name_to_code[$country])) {
			return null;
		}

		return self::$name_to_code[$country];
	}


	/**
	 * Get an array of country codes that use the Euro.
	 *
	 * @return array
	 */
	public static function getEuroCountries()
	{
		return array(
			'AT', // Austria
			'BE', // Belgium
			'CY', // Cyprus
			'FI', // Finland
			'FR', // France
			'DE', // Germany
			'GR', // Greece
			'IE', // Ireland
			'IT', // Italy
			'LU', // Luxembourg
			'MT', // Malta
			'NL', // Netherlands
			'PT', // Portugal
			'SK', // Slovakia
			'SI', // Slovenia
			'ES', // Spain
		);
	}


	/**
	 * Check if a country uses the euro.
	 *
	 * @param   string  $country_code  The coutry to check
	 * @return  bool
	 */
	public static function isEuroCountry($country_code)
	{
		return in_array(strtoupper($country_code), self::getEuroCountries());
	}
}
