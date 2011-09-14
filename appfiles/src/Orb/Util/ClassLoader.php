<?php
/**
 * Orb
 *
 * @package Orb
 * @category Util
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Util;

/**
 * A simple extension to the Symfony class loader that adds ability to map specific
 * classes to specific files. Useful for single-classes.
 */
class ClassLoader extends \Symfony\Component\ClassLoader\UniversalClassLoader
{
	/**
	 * An array of classname => file
	 * @var array
	 */
	protected $class_map = array();

	/**
	 * Maps a namespace to a callback that is called when it cant be loaded using
	 * a normal map.
	 *
	 * @var array
	 */
	protected $namespace_callback = array();


	/**
	 * Get the current class map.
	 *
	 * @return array
	 */
	public function getClassNameMap()
	{
		return $this->class_map;
	}


	/**
	 * Register a new namespace callback loader
	 *
	 * @param string $namespace
	 * @param callback $callback
	 * @return void
	 */
	public function registerNamespaceCallback($namespace, $callback)
	{
		$this->namespace_callback[$namespace] = $callback;
	}


	/**
	 * Register a classname to a particular path.
	 *
	 * @param string $classname The full classname
	 * @param string $path The path to the source file
	 */
	public function registerClassName($class_name, $path)
	{
		$this->class_map[$class_name] = $path;
	}



	/**
	 * Register an array of classnames.
	 *
	 * @param array $class_names An array of classname => path
	 */
	public function registerClassNames(array $class_names)
	{
		foreach ($class_names as $class_name => $path) {
			$this->class_map[$class_name] = $path;
		}
	}


	public function findFile($class_name)
	{
		if (isset($this->class_map[$class_name])) {
			$file = $this->class_map[$class_name];
			if (file_exists($file)) {
				return $file;
			}
		}

		$file = parent::findFile($class_name);

		if ($file === null) {
			$m = null;
			$ns_parts = explode('\\', $class_name, 2);
			if (count($ns_parts) == 2) {
				$ns = $ns_parts[0];
				if (isset($this->namespace_callback[$ns])) {
					$callback = $this->namespace_callback[$ns];
					$file = call_user_func($callback, $class_name);
				}
			}
		}

		return $file;
	}
}
