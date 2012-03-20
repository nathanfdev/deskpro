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
 * @subpackage
 */

namespace Application\DeskPRO\Templating\Loader;

use Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator as BaseTemplateLocator;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Templating\TemplateReferenceInterface;

class TemplateLocator extends BaseTemplateLocator
{
	protected $locator;
	protected $cache = array();

	public function __construct(FileLocatorInterface $locator, $cacheDir = null)
	{
		$cache_file = DP_ROOT.'/sys/template-map.php';
		if (is_file($cache_file)) {
			$this->cache = require $cache_file;
		}

		$this->locator = $locator;
	}

	public function locate($template, $currentPath = null, $first = true)
	{
		if (!$template instanceof TemplateReferenceInterface) {
			throw new \InvalidArgumentException("The template must be an instance of TemplateReferenceInterface.");
		}

		$key = $template->getLogicalName();

		if (isset($this->cache[$key])) {
			return $this->cache[$key]['path'];
		}

		try {
			$this->cache[$key] = array(
				'path' => $this->locator->locate($template->getPath(), $currentPath)
			);
			return $this->cache[$key]['path'];
		} catch (\InvalidArgumentException $e) {
			throw new \InvalidArgumentException(sprintf('Unable to find template "%s" : "%s".', $template, $e->getMessage()), 0, $e);
		}
	}
}