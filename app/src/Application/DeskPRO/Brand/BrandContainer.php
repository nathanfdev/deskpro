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
 * @subpackage Brand
 */

namespace Application\DeskPRO\Brand;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\NewSettings\SettingsBag;
use Application\PortalBundle\Theme\ThemeResolver;

/**
 * The BrandContainer is a hub that holds all of the information that might be needed in the system that relate to a
 * particular brand. It is the context of the brand in question. It encompasses settings, template resolution/rendering,
 * brand information, and anything else you might want to do with a brand. It's created by the BrandFactory.
 */
class BrandContainer 
{
	/**
	 * @var \Application\DeskPRO\Entity\Brand
	 */
	private $brand;

	/**
	 * @var \Application\DeskPRO\NewSettings\SettingsBag
	 */
	private $settings;

	/**
	 * @var \Application\PortalBundle\Theme\ThemeResolver
	 */
	private $theme_resolver;

	/**
	 * @var \Application\PortalBundle\Theme\ThemeInterface
	 */
	private $theme;


	public function __construct(Brand $brand, SettingsBag $settings, ThemeResolver $theme_resolver)
	{
		$this->brand = $brand;
		$this->settings = $settings;
		$this->theme_resolver = $theme_resolver;
		$this->theme = $theme_resolver->getThemeById($brand->theme_id);
	}

	public function getSetting($setting_name)
	{
		return $this->getSettings()->get($setting_name);
	}

	public function getBrand()
	{
		return $this->brand;
	}

	public function getSettings()
	{
		return $this->settings;
	}

	public function resolveController($controller)
	{
		return $this->theme_resolver->controller($this->theme, $controller);
	}
}
 