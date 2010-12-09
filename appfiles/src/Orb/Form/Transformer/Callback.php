<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Form
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Form\Transformer;


/**
 * A transformer that delegates transformation to a callback.
 */
class Callback implements TransformerInterface
{
	protected $transform_stored_fn;
	protected $transform_form_fn;

	public function __construct($transform_stored_fn, $transform_form_fn)
	{
		$this->transform_stored_fn = $transform_stored_fn;
		$this->transform_form_fn = $transform_form_fn;
	}

	public function transformStoredToForm($value)
	{
		return call_user_func_array($this->transform_stored_fn, array($value));
	}

	public function transformFormToStored($value)
	{
		return call_user_func_array($this->transform_form_fn, array($value));
	}
}