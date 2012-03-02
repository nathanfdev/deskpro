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
 * @subpackage Form
 */

namespace Application\DeskPRO\Form;

use Orb\Util\Arrays;
use Orb\Util\Strings;

use Orb\Form\Field\Field;
use Orb\Form\Field\FieldGroup;
use Orb\Form\Field\Form;

/**
 * Helper that takes a form and generates code for JS validations.
 */
class JsValidatorGenerator
{
	/**
	 * @var Orb\Form\Field\Form
	 */
	protected $form;

	protected $js_name;

	protected $field_defs_js = '';
	protected $validator_defs_js = '';

	public function __construct(\Orb\Form\Field\Form $form, $js_name = 'form_validator')
	{
		$this->form = $form;
		$this->js_name = $js_name;
		$this->_processField($form);
	}

	protected function _processField(\Orb\Form\Field\Field $field)
	{
		$js_fields = array();
		$js_vali = array();

		if ($field->getValidators()) {
			$varname = 'field_' . $field->getFormId();
			$js_fields[] = 'var ' . $varname . ' = new DeskPRO.Form.FormField($(\'#' . $field->getFormId() . '\'));';

			foreach ($field->getValidators() as $validator) {
				if ($validator instanceof \Orb\Validator\ZendValidator) {
					$validator = $validator->getZendValidator();
				}

				switch (get_class($validator)) {
					case 'Zend\Validator\StringLength':
						$opts = array(
							'minLength' => $validator->getMin(),
							'maxLength' => $validator->getMax(),
						);
						$js_valid[] = $js_name . '.addValidator('.$varname.', new DeskPRO.Form.Validator.Length(' . json_encode($opts) . '), [\'change\']);';
						break;

					case 'Zend\Validator\Regex':

						$matches = Strings::extractRegexMatch('#^(.)(.*?)($1)([a-z]*)$#', $validator->getPattern(), -1);
						$regex = str_replace("'", "\\'", $matches[1]);

						$regex_js = 'new RegExp(\'' . $regex . '\')';

						$js_valid[] = $js_name . '.addValidator('.$varname.', new DeskPRO.Form.Validator.Regex(' . $regex_js . '), [\'change\']);';

						break;
				}
			}

			$this->field_defs_js .= implode('', $js_fields);
			$this->validator_defs_js .= implode('', $js_vali);

			unset($js_fields, $js_vali);

			if ($field instanceof \Orb\Form\Field\FieldGroup) {
				$this->__processField($field);
			}
		}
	}



	/**
	 * Get the JS that defines fields
	 *
	 * @return string
	 */
	public function getFieldDefinitions()
	{
		return $this->field_defs_js;
	}



	/**
	 * Get the JS that defines simple validators
	 *
	 * @return string
	 */
	public function getValidatorDefinitions()
	{
		return $this->validator_defs_js;
	}
}
