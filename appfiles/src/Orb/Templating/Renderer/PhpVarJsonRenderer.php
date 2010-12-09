<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Templating
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Templating\Renderer;

use \Symfony\Component\Templating\Storage\Storage;
use \Symfony\Component\Templating\Storage\FileStorage;
use \Symfony\Component\Templating\Storage\StringStorage;


/**
 * Works in the same was as PhpVarRenderer except that $OUTPUT is expected to be an
 * array (k=>v), which is then encoded as JSON.
 */
class PhpVarJsonRenderer extends PhpVarRenderer
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
}
