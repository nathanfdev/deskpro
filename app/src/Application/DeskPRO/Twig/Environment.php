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
 * @subpackage Twig
 */

namespace Application\DeskPRO\Twig;

class Environment extends \Twig_Environment
{
	public function __construct(\Twig_LoaderInterface $loader = null, $options = array())
	{
		static $has_done = false;

		if (defined('DP_DEBUG') && (empty($options['auto_reload']) || $options['auto_reload'] === null)) {
			if (DP_DEBUG) {
				$options['auto_reload'] = true;
			} else {
				$options['auto_reload'] = false;
			}
		}

		if (!$has_done) {
			stream_wrapper_register('dptpl', 'Application\\DeskPRO\\Twig\\Loader\\DbStreamWrapper', 0);
		}

		parent::__construct($loader, $options);
	}


	public function loadTemplate($name, $index = null)
    {
        $cls = $this->getTemplateClass($name, $index);

        if (isset($this->loadedTemplates[$cls])) {
            return $this->loadedTemplates[$cls];
        }

        if (!class_exists($cls, false)) {
            if (false === $cache = $this->getCacheFilename($name)) {
                eval('?>'.$this->compileSource($this->loader->getSource($name), $name));
            } else {
				if (strpos($cache, 'dptpl://') === 0) {
					$tplinfo = \Application\DeskPRO\Twig\Loader\DbStreamWrapper::getTemplateInfo(str_replace('dptpl://load/', '', $cache));
					eval('?>'.$tplinfo['template_compiled']);
				} else {
					if (!is_file($cache) || ($this->isAutoReload() && !$this->isTemplateFresh($name, filemtime($cache)))) {
						$this->writeCacheFile($cache, $this->compileSource($this->loader->getSource($name), $name));
					}

					require_once $cache;
				}
            }
        }

        if (!$this->runtimeInitialized) {
            $this->initRuntime();
        }

        return $this->loadedTemplates[$cls] = new $cls($this);
    }


	/**
	 * If theres a custom template with an error, then
	 * we'll try and use the default template instead.
	 *
	 * @param $name
	 * @return string
	 */
	public function markCustomTemplateAsCrashed($name)
	{
		if ($this->loader->dbHasTemplate($name)) {
			$this->loader->markCustomTemplateAsCrashed($name);
		}
	}

	/**
	 * Check if a particular template is a custom template
	 *
	 * @param $name
	 * @return mixed
	 */
	public function isCustomTemplate($name)
	{
		return $this->loader->dbHasTemplate((string)$name);
	}


	public function getCacheFilename($name)
	{
		if (!$this->loader->dbHasTemplate($name)) {
			return parent::getCacheFilename($name);
		}

		return 'dptpl://load/' . $name;
	}

	public function isTemplateFresh($name, $time)
	{
		if ($this->loader->dbHasTemplate($name)) {
			return true;
		}

		return $this->loader->isFresh($name, $time);
	}
}
