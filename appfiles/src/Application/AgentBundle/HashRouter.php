<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AgentBundle;

use Application\DeskPRO\App;
use Symfony\Component\Routing\RouterInterface;

/**
 * This generates JS hash router
 */
class HashRouter
{
	protected $paths = array();
	protected $non_unique = array();

	protected $generator;
	
	public function __construct($generator)
	{
		$this->generator = $generator;
	}

	public function compile($js_classname = null)
	{
		if (!$js_classname) {
			$js_classname = 'window.DeskPRO_HashRouter';
		}

		$js = array();
		$js[] = "$js_classname = {\n\n";

		$js[] = "\tbaseUrl: '',\n\n";
		$js[] = "\tanchors: " . json_encode($this->generator->getAnchorPatternMap()) . ",\n\n";

		$js[] = <<<EOF
	setBaseUrl: function(baseUrl) {
		this.baseUrl = baseUrl.replace(/\/$/, '');
	},

	hasAnchor: function(anchor_name) {
		if (this.anchors[anchor_name] !== undefined) {
			return true;
		}

		return false;
	},

	getAnchorPattern: function(anchor_name) {
		return this.anchors[anchor_name] || '';
	},

	getUrl: function(anchor_name, args) {
		var pattern = this.getAnchorPattern(anchor_name);

		var matches = pattern.match(/\{(.*?)\}/g);
		var m = null;
		var val = null;
		for (var i = 0; i < matches.length; i++) {
			m = matches[i];
			if (args[i] === undefined) {
				console.warn('Anchor %s was not provided with enough args: %o', anchor_name, args);
				break;
			}

			var val = args[i];
			if (typeof val == 'function') {
				val = val();
			}

			pattern = pattern.replace(m+'', args[i]);
		}

		return this.baseUrl + pattern;
	},

	getUrlNamedArgs: function(anchor_name, args) {
		var pattern = this.getAnchorPattern(anchor_name);

		Object.each(args, function(v,k) {
			if (typeof v == 'function') {
				v = v();
			}

			pattern = pattern.replace('{' + k + '}', v);
		});

		return this.baseUrl + pattern;
	}
}
EOF;

		return implode('', $js);
	}
}