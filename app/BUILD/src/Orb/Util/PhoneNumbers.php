<?php

/**
 * DeskPRO.
 */

namespace Orb\Util;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

/**
 * A wrapper around the php libphonenumber library.
 *
 * Mostly just public static methods to make it easier to use.
 *
 * https://github.com/davideme/libphonenumber-for-PHP
 */
class PhoneNumbers
{
    //########################################################
    // Phone number TYPES
    // - Provided by libphonenumber, we copy them here because we store this numeric value.
    // its unlikely the lib would ever change these values, but just in case they ever did,
    // we reference this class for numeric translation to text.
    //########################################################
    const FIXED_LINE           = 0;
    const MOBILE               = 1;
    const FIXED_LINE_OR_MOBILE = 2;
    const TOLL_FREE            = 3;
    const PREMIUM_RATE         = 4;
    const SHARED_COST          = 5;
    const VOIP                 = 6;
    const PERSONAL_NUMBER      = 7;
    const PAGER                = 8;
    const UAN                  = 9;
    const UNKNOWN              = 10;
    const EMERGENCY            = 27;
    const VOICEMAIL            = 28;
    const SHORT_CODE           = 29;
    const STANDARD_RATE        = 30;

    /**
     * Takes an int type (one of the constants of this class) returned via self::getTypeCode(numberString)
     * and turns it into a simpler readable string.
     *
     * @param $statusCode
     *
     * @return string
     */
    public static function getTypeString($statusCode)
    {
        switch ($statusCode) {
            case self::FIXED_LINE:
                return 'landline'; // we are quite confident it is a landline
            case self::MOBILE:
                return 'mobile'; // we are quite confident it is a cell
            case self::FIXED_LINE_OR_MOBILE:
                return 'landline-or-mobile'; // it can be either a landline or a cell (canada, us, etc do this)
            case self::VOIP:
                return 'voip'; // we are pretty sure its a VOIP number
            case self::TOLL_FREE:
                return 'toll-free';
            case self::EMERGENCY:
                return 'emergency';
            default:
                return 'unknown';
        }
    }

    /**
     * Shorcut method to get our string representation of the type of a number, without needing to worry about
     * the numeric type code.
     *
     * @param $phone_number
     *
     * @throws NumberParseException Make sure to validate the number string before using this
     *
     * @return string
     */
    public static function getType($phone_number)
    {
        return self::getTypeString(self::getTypeCode($phone_number));
    }

    /**
     * @param $phone_number
     *
     * @throws NumberParseException Make sure to validate the number string before using this
     *
     * @return int the number code that libphonenumber uses (one of the constants of this class)
     */
    public static function getTypeCode($phone_number)
    {
        $phone_util = PhoneNumberUtil::getInstance();
        $number     = $phone_util->parse($phone_number, null);

        return $phone_util->getNumberType($number) ?: self::UNKNOWN;
    }

    /**
     * @param string $phone_number
     *
     * @return bool true if it looks like a valid number, false otherwise
     */
    public static function isValid($phone_number)
    {
        if (self::looksEmpty($phone_number)) {
            return false;
        }

        try {
            $phone_util = PhoneNumberUtil::getInstance();
            $number     = $phone_util->parse($phone_number, null);
        } catch (\Exception $e) {
            return false;
        }

        return $phone_util->isValidNumber($number);
    }

    public static function parseNum($phone_number)
    {
        $phone_util = PhoneNumberUtil::getInstance();

        $libnum = $phone_util->parse($phone_number, null);

        return (string) $libnum;
    }

    /**
     * @param $phone_number
     *
     * @throws NumberParseException Make sure to validate the number string before using this
     */
    public static function guessType($phone_number)
    {
        $phone_util = PhoneNumberUtil::getInstance();
        $number     = $phone_util->parse($phone_number, null);

        return $phone_util->getNumberType($number);
    }

    /**
     * @param string $phone_number
     *
     * @throws NumberParseException Make sure to validate the number string before using this
     *
     * @return string The phone number in E.164 format
     */
    public static function toE164Format($phone_number)
    {
        $phone_util = PhoneNumberUtil::getInstance();
        $number     = $phone_util->parse($phone_number, null);

        return $phone_util->format($number, PhoneNumberFormat::E164);
    }

    /**
     * @param string $phone_number
     *
     * @throws NumberParseException Make sure to validate the number string before using this
     *
     * @return string The phone number in International format
     */
    public static function toInternationalFormat($phone_number)
    {
        $phone_util = PhoneNumberUtil::getInstance();
        $number     = $phone_util->parse($phone_number, null);

        return $phone_util->format($number, PhoneNumberFormat::INTERNATIONAL);
    }

    /**
     * @param string $phone_number
     *
     * @throws NumberParseException Make sure to validate the number string before using this
     *
     * @return string The phone number in National format
     */
    public static function toNationalFormat($phone_number)
    {
        $phone_util = PhoneNumberUtil::getInstance();
        $number     = $phone_util->parse($phone_number, null);

        return $phone_util->format($number, PhoneNumberFormat::NATIONAL);
    }

    /**
     * Pass in a phone number string to get the ISO 161-1 country code
     * i.e. "GB" or "CA", or "US", etc.
     *
     * @param $phone_number
     *
     * @throws NumberParseException Make sure to validate the number string before using this
     *
     * @return null|string
     */
    public static function getRegionForNumber($phone_number)
    {
        $phone_util = PhoneNumberUtil::getInstance();
        $number     = $phone_util->parse($phone_number, null);

        return $phone_util->getRegionCodeForNumber($number);
    }

    /**
     * Since phone numbers come in as raw text, and our front-end tools
     * might leave some undesired defaults (eg. "+1 (" as a blank string)
     * we need to have a phone number related function to tell us if the
     * string we are looking at should be treated as null. If it "looksEmpty"
     * its probably fine to ignore the input.
     *
     * @param string $phone_number
     *
     * @return bool
     */
    public static function looksEmpty($phone_number)
    {
        $phone_number = trim($phone_number);

        return strlen($phone_number) < 7;
    }
}
