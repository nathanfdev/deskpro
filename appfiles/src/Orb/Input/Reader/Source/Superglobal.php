<?php
/**
 * Orb
 *
 * @package Orb
 * @category Input
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Orb\Input\Reader\Source;

/**
 * A reader source that fetches data from a superglobal array.
 */
class Superglobal implements SourceInterface
{
	/**
	 * The superglobal name
	 * @var string
	 */
	protected $superglobal;

	/**
	 * Array of data
	 * @var array
	 */
	protected $array = null;

	/**
	 * Create the source.
	 *
	 * @param  $sg_name  The name of the superglobal: _POST, _GET etc.
	 */
	public function __construct($sg_name)
	{
		$this->superglobal = $sg_name;
	}



	/**
	 * Get the value of some variable
	 *
	 * @param   string|array  $name     The name of the variable
	 * @param   mixed         $options  Any options there may be
	 * @return  mixed
	 */
	public function getValue($name, $options = null)
	{
		$this->_initArray();

		$parts = array();
		if (is_array($name)) {
			$parts = $name;
			$name = array_shift($parts);
		}

		if (isset($this->superglobal[$name])) {
			$value = $this->superglobal[$name];
		} else {
			$value = null;
		}

		if ($parts) {
			foreach ($parts as $part) {

				if (!is_array($value) OR !isset($value[$part])) {
					$value = null;
					break;
				}

				$value = $value[$part];
			}
		}

		return $value;
	}

	protected function _initArray()
	{
		if ($this->array !== null) return; // already done

		// We'll enforce our own request array
		if ($this->superglobal == '_REQUEST') {
			$this->array = \array_merge($_GET, $_POST);
		} else {
			$this->array = $GLOBALS[$this->superglobal];
		}
		if (!$this->array) $this->array = array();

		// Process slashes
		if (\get_magic_quotes_gpc()) {
			Orb\Util\Arrays::func($this->array, 'stripslashes');
		}
	}



	/**
	 * Check if a value of some variable is set.
	 *
	 * @param   string|array  $name     The name of the variable
	 * @param   mixed         $options  Any options there may be
	 * @return  bool
	 */
	public function checkIsset($name, $options = null)
	{
		return ($this->getValue($name, $options) === null ? false : true);
	}



	/**
	 * Get the superglobal name.
	 *
	 * @return string
	 */
	public function getSuperglobalName()
	{
		return $this->superglobal;
	}
}