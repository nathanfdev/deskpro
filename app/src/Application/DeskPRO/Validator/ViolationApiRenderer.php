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
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Validator;

use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class ViolationApiRenderer
{
	/**
	 * @var array
	 */
	private $aliases = array();


	public function __construct()
	{
		// Default aliases
		$this->addClassCodeName('Application\\DeskPRO\\Entity', 'Entity');
	}


	/**
	 * Adds a codename for a class (or namespace).
	 *
	 * @param string $classname
	 * @param string $codename
	 */
	public function addClassCodeName($classname, $codename)
	{
		$this->aliases[$classname] = $codename;
	}


	/**
	 * Gets the codename for a class.
	 *
	 * @param string $classname
	 * @return string
	 */
	public function getCodeName($classname)
	{
		$parts = explode('\\', $classname);
		$alias = null;
		$ends = array();
		do {
			$part_string = implode('\\', $parts);
			if (isset($this->aliases[$part_string])) {
				$alias = $this->aliases[$part_string];
				break;
			}
			$ends[] = array_pop($parts);
		} while ($parts);

		if ($alias) {
			if ($ends) {
				$alias .= '\\' . implode('\\', $ends);
			}
		} else {
			$alias = $classname;
		}

		return str_replace('\\', '.', $alias);
	}


	/**
	 * @param ConstraintViolation $err
	 * @return array
	 */
	public function renderViolation(ConstraintViolation $err)
	{
		$val = $err->getRoot();
		if (is_object($val)) {
			if ($val instanceof \Doctrine\ORM\Proxy\Proxy) {
				$classname = get_parent_class($val);
			} else {
				$classname = get_class($val);
			}

			$field_id = $this->getCodeName($classname);
		} else {
			$field_id = $err->getPropertyPath();
		}

		$code = $err->getCode();
		$message = $err->getMessage();

		if (preg_match('#^\[([a-zA-Z0-9\-_\.]+)\](.*)$#', $message, $m)) {
			$message = trim($m[2]);

			if (!$code) {
				$code = $m[1];
			}
		}

		if (!$code) {
			$code = 'undefined';
		}

		return array(
			'field_id'  => $field_id,
			'prop_path' => $err->getPropertyPath(),
			'prop'      => $err->getPropertyPath(),
			'code'      => $code,
			'message'   => $message,
		);
	}


	/**
	 * Renders a list of validation errors
	 *
	 * @param ConstraintViolationList $list
	 * @return array
	 */
	public function renderViolationList(ConstraintViolationList $list)
	{
		$info = array(
			'errors' => array(),
			'error_codes' => array(),
		);

		foreach ($list as $err) {
			$err_info = $this->renderViolation($err);
			$info['errors'][] = $err_info;
			$info['error_codes'][] = $err_info['prop'] . '.' . $err_info['code'];
		}

		return $info;
	}


	/**
	 * Renders multiple violations into a single list. Ideal if you have different components using different validators, but you need
	 * to return a single validation error response.
	 *
	 * @param ConstraintViolationList[] $lists  A key=>ConstraintViolationList map, where the key is used to prefix the paths of the errors in the list
	 * @return array
	 */
	public function renderCombinedViolationList(array $lists)
	{
		$mega_list = array(
			'errors' => array(),
			'error_codes' => array(),
		);

		foreach ($lists as $prefix => $list) {
			$info = $this->renderViolationList($list);

			foreach ($info['errors'] as $err) {
				$err['prop'] = $prefix . '.' . $err['prop'];
				$mega_list['errors'][] = $err;
			}

			foreach ($info['error_codes'] as $err_code) {
				$mega_list['error_codes'] = $prefix . '.' . $err_code;
			}
		}

		return $mega_list;
	}
}