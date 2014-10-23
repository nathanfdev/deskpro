<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Theme
 */

namespace Application\PortalBundle\Theme;

class ThemeResolver 
{
	/**
	 * @var ThemeRepository
	 */
	private $theme_repo;

	public function __construct(ThemeRepository $theme_repo)
	{
		$this->theme_repo = $theme_repo;
	}

	/**
	 * @param $theme_id
	 * @return ThemeInterface
	 */
	public function getThemeById($theme_id)
	{
		return $this->theme_repo->find($theme_id);
	}


	/**
	 * @param ThemeInterface $theme
	 * @param                $input_controller
	 * @return null|string
	 * @throws \RuntimeException
	 */
	public function controller(ThemeInterface $theme, $input_controller)
	{
		if (
			is_string($input_controller)
			&& 'Theme:' === substr($input_controller, 0, 6)
			&& 3 === count($parts = explode(':', $input_controller))
		) {
			$controller = $parts[1];
			$action     = $parts[2];

			$try = $theme->getNamespace() . '\\Controller\\' . $controller . 'Controller';
			if (class_exists($try)) {
				$callable = $try . '::' . $action . 'Action';
				if (is_callable($callable)) {
					return $callable;
				}
			}

			if ($parent = $theme->getParent()) {
				return $this->controller($parent, $input_controller);
			}

			throw new \RuntimeException(sprintf('could not resolve theme controller "%s"', $input_controller));
		}

		return null;
	}


	/**
	 * Get the absolute path to a filename for a theme, with the name format like:
	 *
	 * Theme:Portal:index.html.twig
	 * ThemeParent:Portal:index.html.twig
	 *
	 * @param ThemeInterface $theme
	 * @param                $name
	 * @return string
	 */
	public function templatePath(ThemeInterface $theme, $name)
	{
		if (
			is_string($name)
			&& 'Theme:' === substr($name, 0, 6)
			&& 3 === count($parts = explode(':', $name))
		) {
			$controller = $parts[1];
			$filename   = $parts[2];

			$template_file = sprintf('%s/%s/%s', $theme->getBaseTemplateDir(), $controller, $filename);

			if (file_exists($template_file)) {
				return $template_file;
			}

			return $this->tryParent($theme, $name);
		}

		if (
			is_string($name)
			&& 'ThemeParent:' === substr($name, 0, 12)
			&& 3 === count($parts = explode(':', $name))
		) {
			$converted_theme_name = 'Theme:' . substr($name, 12);
			return $this->tryParent($theme, $converted_theme_name);
		}

		return null;
	}


	public function tryParent(ThemeInterface $theme, $name)
	{
		if ($parent = $theme->getParent()) {
			return $this->templatePath($parent, $name);
		}

		return null;
	}
}
 