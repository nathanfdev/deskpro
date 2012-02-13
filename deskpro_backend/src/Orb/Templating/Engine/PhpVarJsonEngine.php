<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Templating
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Templating\Engine;

use \Symfony\Component\Templating\Storage\Storage;


/**
 * Works in the same was as PhpVarRenderer except that $OUTPUT is expected to be an
 * array (k=>v), which is then encoded as JSON.
 */
class PhpVarJsonEngine extends PhpVarEngine
{
	protected function _preProcess(Storage $template, array $parameters = array())
	{
		return array();
	}

	protected function _postProcess($OUTPUT)
	{
		if (!is_array($OUTPUT)) {
			$OUTPUT = array((string)$OUTPUT);
		}

		return json_encode($OUTPUT);
	}

	public function supports($name)
	{
		return false !== strpos($name, '.jsonphp');
	}
}
