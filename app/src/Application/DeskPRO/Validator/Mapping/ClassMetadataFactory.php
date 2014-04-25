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
 * @category Validator
 */

namespace Application\DeskPRO\Validator\Mapping;

use Orb\Util\Strings;
use Orb\Util\Util;
use Symfony\Component\Validator\Exception\NoSuchMetadataException;
use Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory;

/**
 * This custom factory is the same as the default, except we intercept constraints and prefix the default
 * error messages with the classname. For example, "This value should not be blank." becomes "[NotBlank] This value should not be blank.".
 *
 * We do this so we can add code-names to validators. When we render validator errors (see ViolationApiRenderer), we then parse out the
 * code names.
 *
 * Templates/API will use code names. But the readable English error messages are good as well (for example, makes API more human-friendly).
 */
class ClassMetadataFactory extends LazyLoadingMetadataFactory
{
	/**
	 * Array of classnames we've done
	 *
	 * @var array
	 */
	private $done_classes = array();

	/**
	 * {@inheritDoc}
	 */
	public function getMetadataFor($value)
	{
		if (!is_object($value) && !is_string($value)) {
			throw new NoSuchMetadataException(sprintf('Cannot create metadata for non-objects. Got: %s', gettype($value)));
		}

		$class = ltrim(is_object($value) ? get_class($value) : $value, '\\');
		$metadata = parent::getMetadataFor($value);

		if (isset($this->done_classes[$class])) {
			return $metadata;
		}

		$this->done_classes[$class] = true;

		foreach ($metadata->constraints as $c) {
			$this->procConstraint($c);
		}
		foreach ($metadata->getters as $g) {
			foreach ($g->constraints as $c) {
				$this->procConstraint($c);
			}
		}
		foreach ($metadata->properties as $p) {
			foreach ($p->constraints as $c) {
				$this->procConstraint($c);
			}
		}
		foreach ($metadata->members as $m) {
			if ($m && !empty($m->constraints)) {
				foreach ($m->constraints as $c) {
					$this->procConstraint($c);
				}
			}
		}

		return $metadata;
	}

	private function procConstraint($constraint)
	{
		foreach ($constraint as $prop => $val) {
			if (($prop == 'message' || preg_match('#Message$#', $prop)) && is_string($val) && !preg_match('#^\[[a-zA-Z0-9_\-\.]+\]#', $val)) {
				$name = Util::getBaseClassname($constraint);
				$name = preg_replace('#Constraint$#', '', $name); // some have a Constraint suffix which we dont want
				$name = Strings::camelCaseToUnderscore($name);

				// This is for case when we have pluralization cases for constraint message that are divided by '|'
				if (strpos($val, '|') !== false) {
					$parts = explode('|', $val);

					foreach ($parts as $key => $part) {
						$parts[$key] = '[' . $name . '] ' . $part;
					}

					$val = implode('|', $parts);
					$constraint->$prop = $val;
				} else {
					$constraint->$prop = '[' . $name . '] ' . $val;
				}
			}
		}

		// Some constraints are collections of other constraints (collection related validators)
		if (!empty($constraint->constraints)) {
			foreach ($constraint->constraints as $c) {
				$this->procConstraint($c);
			}
		}
	}
}