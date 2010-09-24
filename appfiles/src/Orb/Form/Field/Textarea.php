<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Form
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Orb\Form\Field;

use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * A textarea field
 */
class Textarea extends Field
{
	protected function init()
	{
		#------------------------------
		# Automatically set up a string length validator
		#------------------------------

		if ($this->hasOption('max_length') OR $this->hasOption('min_length')) {

			$options = array();
			if ($this->hasOption('max_length')) $options['max']      = $this->getOption('max_length');
			if ($this->hasOption('min_length')) $options['min']      = $this->getOption('min_length');
			if ($this->hasOption('encoding'))   $options['encoding'] = $this->getOption('encoding');

			$validator = \Orb\Validator\ZendValidator::factory(
				'Zend\\Validator\\StringLength',
				$options
			);

			$this->addValidator($validator);
		}
	}

	public function getDefaultAttributes()
	{
		$attr = parent::getDefaultAttributes();
		$attr['cols'] = 40;
		$attr['rows'] = 5;

		unset($attr['value']);

		return $attr;
	}
}