<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\UI;

use Orb\Util\Arrays;

/**
 * Handling around the JS "rule builder" widget
 */
class RuleBuilder
{
	protected $special_keys = array();

	/**
	 * The "Actions" builder has a 'type' item and then an options array.
	 *
	 * A typical result might look like:
	 * <code>
	 * array(
	 *     array('type' => 'urgency', 'options' => array('num' => 22)),
	 *     array('type' => 'priority_id', 'options' => array('priority_id' => 5)),
	 * )
	 * </code>
	 *
	 * @return \Application\DeskPRO\UI\RuleBuilder
	 */
	public static function newActionsBuilder()
	{
		return new self(array('type'));
	}


	/**
	 * The "Terms" builder has a 'type' item and an 'op' item, and then an options array.
	 *
	 * A typical result might look like:
	 * <code>
	 * array(
	 *     array('type' => 'urgency', 'op' => 'ltg', 'options' => array('num' => 22)),
	 *     array('type' => 'priority_id', 'op' => 'is', 'options' => array('priority_id' => 5)),
	 * )
	 * </code>
	 *
	 * @return \Application\DeskPRO\UI\RuleBuilder
	 */
	public static function newTermsBuilder()
	{
		return new self(array('type', 'op'));
	}


	/**
	 * Special keys are keys we'll find in the form that aren't options.
	 *
	 * @param array $special_keys
	 */
	public function __construct(array $special_keys)
	{
		$this->special_keys = $special_keys;
	}


	/**
	 * Read an array of terms based from the form, using structure defined.
	 * 
	 * @param array $form
	 * @return array
	 */
	public function readForm(array $form)
	{
		$data = array();

		foreach ($form as $item) {
			if (!is_array($item)) continue;

			$data_item = array();
			foreach ($this->special_keys as $k) {
				$data_item[$k] = null;
			}
			$data_item['options'] = array();

			foreach ($item as $k => $v) {
				// Special value means not to add the term
				// Used for things like "any" where the term shouldnt
				// be applied.
				if ($v == 'DP_DISCARD_TERM') {
					continue 2;
				}
				
				if (in_array($k, $this->special_keys)) {
					$data_item[$k] = $v;
				} else {
					$data_item['options'][$k] = $v;
				}
			}

			$data[] = $data_item;
		}

		return $data;
	}
}