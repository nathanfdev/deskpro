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

namespace Application\PortalBundle\Twig;

use Application\DeskPRO\Brand\BrandStack;
use Application\DeskPRO\EntityRepository\Template;
use Twig_Error_Loader;

class PortalLoader implements \Twig_LoaderInterface
{
	/**
	 * @var \Application\DeskPRO\Brand\BrandStack
	 */
	private $brand_stack;

	/**
	 * @var \Application\DeskPRO\EntityRepository\Template
	 */
	private $template_repo;


	public function __construct(BrandStack $brand_stack, Template $template_repo)
	{
		$this->brand_stack = $brand_stack;
		$this->template_repo = $template_repo;
	}

	/**
	 * Gets the source code of a template, given its name.
	 *
	 * @param string $name The name of the template to load
	 * @return string The template source code
	 * @throws Twig_Error_Loader When $name is not found
	 */
	public function getSource($name)
	{
		$brand_container = $this->getBrandContainer();

		if ($template = $this->template_repo->getBrandTemplate($name, $brand_container->getBrand(), $brand_container->getTheme())) {
			return $template->template_code;
		}

		if ($path = $brand_container->resolveTemplatePath((string) $name)) {
			return file_get_contents($path);
		}

		throw new Twig_Error_Loader('could not find theme template "'.$name.'"');
	}


	/**
	 * Gets the cache key to use for the cache for a given template name.
	 *
	 * @param string $name The name of the template to load
	 * @return string The cache key
	 * @throws Twig_Error_Loader When $name is not found
	 */
	public function getCacheKey($name)
	{
	    if (!$brand = $this->brand_stack->getActive()) {
		    $this->brand_stack->push($this->brand_stack->getDefault());
		    $brand = $this->brand_stack->getActive();
	    }
		return $brand->getBrand()->theme_id.$name.$this->brand_stack->getActive()->getBrand()->id;
	}


	/**
	 * Returns true if the template is still fresh.
	 *
	 * @param string    $name The template name
	 * @param timestamp $time The last modification time of the cached template
	 * @return bool    true if the template is fresh, false otherwise
	 * @throws Twig_Error_Loader When $name is not found
	 */
	public function isFresh($name, $time)
	{
		$brand_container = $this->getBrandContainer();

		// If a DB template exists, check its update_at value
		if ($template = $this->template_repo->getBrandTemplate(
			$name, $brand_container->getBrand(), $brand_container->getTheme()
		)
		) {
			return $template->date_updated->getTimestamp() <= $time;
		}

		// FOUND A FLAW
		// if you have a templae in DB and it is deleted, the cached version will still appear due to the below
		// solution is to mark a template as deleted=1 and have the loader ignore deleted=1 templates.

		return filemtime($brand_container->resolveTemplatePath((string)$name)) <= $time;
	}


	/**
	 * @return \Application\DeskPRO\Brand\BrandContainer
	 * @throws \RuntimeException
	 */
	protected function getBrandContainer()
	{
		if (!$brand_container = $this->brand_stack->getActive()) {
			throw new \RuntimeException('no brand is active in the brand stack. cannot fetch a theme template.');
		}

		return $brand_container;
	}
}
 