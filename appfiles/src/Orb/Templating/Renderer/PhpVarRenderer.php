<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Templating
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Templating\Renderer;

use \Symfony\Component\Templating\Renderer\Renderer;
use \Symfony\Component\Templating\Storage\Storage;
use \Symfony\Component\Templating\Storage\FileStorage;
use \Symfony\Component\Templating\Storage\StringStorage;


/**
 * This renderer is like a normal PHP renderer except that the value is taken from
 * a special $OUTPUT variable. This is useful in cases where lots PHP processing is
 * taking place, or in cases where you don't want superfluous whitespace etc.
 */
class PhpVarRenderer extends Renderer
{
	public function evaluate(Storage $template, array $parameters = array())
	{
		$OUTPUT = $this->_preProcess($template, $parameters);
		$__template__ = $template;

		extract($parameters, EXTR_SKIP);
		$view = $this->engine;

		ob_start();

		if ($__template__ instanceof FileStorage) {
			extract($parameters);
			$view = $this->engine;
			require $__template__;
		} elseif ($__template__ instanceof StringStorage) {
			eval('; ?>'.$__template__.'<?php ;');
		}

		ob_end_clean();

		if (!isset($OUTPUT)) {
			$OUTPUT = '';
		}

		return $this->_postProcess($OUTPUT);
	}

	protected function _preProcess(Storage $template, array $parameters = array())
	{
		return '';
	}

	protected function _postProcess($OUTPUT)
	{
		if (is_array($OUTPUT)) {
			$OUTPUT = implode('', $OUTPUT);
		}

		return (string)$OUTPUT;
	}
}
