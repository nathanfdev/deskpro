<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Routing\Generator\Dumper;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Generator\Dumper\PhpGeneratorDumper as BasePhpGeneratorDumper;

use Application\DeskPRO\App;

use Orb\Util\Strings;

/**
 * This URL generator sets a default _locale part with the current Translator locale.
 */
class PhpGeneratorDumper extends BasePhpGeneratorDumper
{
    public function dump(array $options = array())
	{
		$class = trim(parent::dump($options));

		list($var_code, $method_code) = $this->getClassCode();

		// First opening brace, as in class {
		$pos = strpos($class, '{') + 1;
		$class = Strings::inject("\n" . $var_code . "\n", $class, $pos);

		// Last closing brace, as in } at the end of the class
		$pos = strrpos($class, '}');
		$class = Strings::inject("\n" . $method_code . "\n", $class, $pos);

		return $class;
	}

	protected function getClassCode()
	{
		$route_patterns = array();
		$anchor_names   = array();

		foreach ($this->getRoutes()->all() as $name => $route) {

			$route_patterns[$name] = $route->getPattern();

			$a_name = $route->getOption('anchor_name');
			if ($a_name) {
				$anchor_names[$a_name] = $name;
			}
		}

		$var_code = array();
		$var_code['routePatterns'] = 'static private $routePatterns = ' . var_export($route_patterns, true) . ';';
		$var_code['anchorNames']   = 'static private $anchorNames = ' . var_export($anchor_names, true) . ';';
		$var_code = implode("\n", $var_code);

		$method_code = <<<EOF
	public function getRoutePattern(\$route_name)
	{
		return isset(self::\$routePatterns[\$route_name]) ? self::\$routePatterns[\$route_name] : null;
	}

	public function getRoutePatterns()
	{
		return self::\$routePatterns;
	}

	public function getAnchorNames()
	{
		return array_keys(self::\$anchorNames);
	}

	public function getRouteForAnchor(\$anchor_name)
	{
		return isset(self::\$anchorNames[\$anchor_name]) ? self::\$anchorNames[\$anchor_name] : null;
	}

	public function getPatternForAnchor(\$anchor_name)
	{
		\$route_name = \$this->getRouteForAnchor(\$anchor_name);
		if (!\$route_name) return null;

		return \$this->getRoutePattern(\$route_name);
	}

	public function getAnchorPatternMap()
	{
		\$map = array();
		foreach (\$this->getAnchorNames() as \$anchor_name) {
			\$map[\$anchor_name] = \$this->getPatternForAnchor(\$anchor_name);
		}

		return \$map;
	}
EOF;

		return array($var_code, $method_code);
	}
}
