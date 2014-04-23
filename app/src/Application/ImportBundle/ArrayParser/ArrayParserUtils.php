<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

/**
 * @package Importer
 */

namespace Application\ImportBundle\ArrayParser;

use Orb\Util\Arrays;
use Orb\Util\Numbers;
use Orb\Util\Strings;

class ArrayParserUtils
{
	private function __construct() {}


	/**
	 * @param string $type
	 * @param string $field
	 * @param array $data
	 * @param mixed $value
	 */
	public static function copyValue($type, $field, array $data, &$value)
	{
		if (!isset($data[$field])) {
			return;
		}

		$clean_val = function($type, $v) use (&$clean_val) {
			switch ($type) {
				case 'raw':
					return $v;
					break;
				case 'int':
					return (int)$v;
					break;
				case 'bool':
					if ($v === 1 || $v === true || $v === "1" || $v === "true" || $v === "yes") {
						return true;
					} else {
						return false;
					}
				case 'string':
					return (string)$v;
					break;
				case 'lstring':
					return Strings::utf8_strtolower((string)$v);
					break;
				case 'array':
					return is_array($v) ? array_values($v) : array($v);
					break;
				case 'karray':
					return is_array($v) ? $v : array($v);
					break;
				case 'date':
					try {
						if (Numbers::isInteger($v)) {
							return new \DateTime("@$v");
						} else {
							return \DateTime::createFromFormat('Y-m-d H:i:s', $v);
						}
					} catch (\Exception $e) {
						return null;
					}
					break;
				default:
					// type[] -- string[] means array of strings, etc
					if (preg_match('#^(.*?)\[\]$#', $type, $m)) {

						if (!is_array($v)) {
							$v = array($v);
						}
						$v = array_values($v);

						foreach ($v as &$subv) {
							$subv = $clean_val($m[1], $subv);
						}
						unset($subv);
						return $v;
					} else {
						throw new \InvalidArgumentException("Unknown type: $type");
					}
			}
		};

		return $value->$field = $clean_val($type, $data[$field]);
	}


	/**
	 * @param array $mapping
	 * @param array $data
	 * @param mixed $value
	 */
	public static function copyValueMapping(array $mapping, array $data, &$value)
	{
		foreach ($mapping as $f => $t) {
			self::copyValue($t, $f, $data, $value);
		}
	}


	/**
	 * @param array $data
	 * @return array
	 */
	public static function cleanArray(array $data)
	{
		foreach ($data as &$v) {
			if (is_string($v)) {
				$v = trim($v);
				$v = Strings::utf8_bad_strip($v);
			} else if (is_array($v)) {
				$v = self::cleanArray($v);
			}
		}
		unset($v);

		$data = Arrays::removeEmptyString($data);
		$data = Arrays::removeEmptyArray($data);
		$data = Arrays::removeNull($data);

		return $data;
	}


	/**
	 * @param string|int $value
	 * @return \DateTime|null
	 */
	public static function parseDateValue($value)
	{
		try {
			if (Numbers::isInteger($value)) {
				return new \DateTime("@$value");
			} else {
				return \DateTime::createFromFormat('Y-m-d H:i:s', $value);
			}
		} catch (\Exception $e) {
			return null;
		}
	}
}