<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Form
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Form\Field;

use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * A text input field
 *
 * @option  bool    max_length  Automatically adds 'maxlength' HTML attribute, and also adds
 *                              a max length validator.
 * @option  bool    min_length  Adds a min length validator.
 * @option  string  encoding    The text encoding (needed for good length tests). Defaults to utf8.
 */
class Text extends Field
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

		
		#------------------------------
		# A single line text field shouldnt have linebreaks
		#------------------------------

		$this->addFilter(new \Zend\Filter\StripNewlines());

	}

	public function getDefaultAttributes()
	{
		$attr = parent::getDefaultAttributes();
		
		if ($this->hasOption('max_length')) {
			$attr['maxlength'] = $this->getOption('max_length');
		}

		return $attr;
	}


	public function __toString()
	{
		return $this->getData();
	}
}