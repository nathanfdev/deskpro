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
	 * Get the current class map.
	 *
	 * @return array
	 */
	public function getClassNameMap()
	{
		return $this->class_map;
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



	/**
	 * Loads the given class or interface.
	 *
	 * @param string $class The name of the class
	 */
	public function loadClass($class_name)
	{
		if (isset($this->class_map[$class_name])) {
			$file = $this->class_map[$class_name];
			if (file_exists($file)) {
				require $file;
			}
			return;
		}

		return parent::loadClass($class_name);
	}
}