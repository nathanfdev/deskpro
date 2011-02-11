<?php

// - These prototypes for some intl stuff
// are bare basics required to get a few Symfony form
// components to work without the actual intl component.
// - So obviously some options/validation on form components that
// need the extension may be limited. However, it doesn't affect
// the basic fields (text, choice, etc).

if (!class_exists('IntlDateFormatter')) {
	class IntlDateFormatter
	{
		const FULL = 0;
		const LONG = 1;
		const MEDIUM = 2;
		const SHORT = 3;
		const NONE = -1;
		const GREGORIAN = 1;
		const TRADITIONAL = 0;

		public function __construct () {}

		public static function create () {}

		public function getDateType () {}

		public function getTimeType () {}

		public function getCalendar () {}

		public function setCalendar () {}

		public function getTimeZoneId () {}

		public function setTimeZoneId () {}

		public function setPattern () {}

		public function getPattern ()
		{
			return 'Y-m-d';
		}

		public function getLocale () {}

		public function setLenient () {}

		public function isLenient () {}

		public function format ($value) {
			return date('Y-m-d', $value);
		}

		public function parse () {}

		public function localtime () {}

		public function getErrorCode () { return 0; }

		public function getErrorMessage () { return ''; }
	}
}

if (!class_exists('Locale')) {
	class Locale
	{
		const ACTUAL_LOCALE = 0;
		const VALID_LOCALE = 1;
		const DEFAULT_LOCALE = null;
		const LANG_TAG = "language";
		const EXTLANG_TAG = "extlang";
		const SCRIPT_TAG = "script";
		const REGION_TAG = "region";
		const VARIANT_TAG = "variant";
		const GRANDFATHERED_LANG_TAG = "grandfathered";
		const PRIVATE_TAG = "private";

		public static function getDefault () { return 'en_US'; }

		public static function setDefault () {}

		public static function getPrimaryLanguage () {}

		public static function getScript () {}

		public static function getRegion () {}

		public static function getKeywords () {}

		public static function getDisplayScript () {}

		public static function getDisplayRegion () {}

		public static function getDisplayName () {}

		public static function getDisplayLanguage () {}

		public static function getDisplayVariant () {}

		public static function composeLocale ( ) {}

		public static function parseLocale () {}

		public static function getAllVariants () {}

		public static function filterMatches () {}

		public static function lookup () {}

		public static function canonicalize () {}

		public static function acceptFromHttp () {}
	}
}

if (!function_exists('intl_get_error_code')) {
	function intl_get_error_code() {
		return 0;
	}
}