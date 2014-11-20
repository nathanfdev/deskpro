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
use Application\DeskPRO\NewSettings\SettingsResolver;
use Application\PortalBundle\Theme\ThemeResolver;

/**
 * The BrandContainerFactory creates BrandContainers for us
 */
class BrandContainerFactory
{
    /**
     * @var \Application\DeskPRO\NewSettings\SettingsResolver
     */
    private $settings_resolver;

    /**
     * @var \Application\PortalBundle\Theme\ThemeResolver
     */
    private $theme_resolver;

    public function __construct(SettingsResolver $settings_resolver, ThemeResolver $theme_resolver)
    {
        $this->settings_resolver = $settings_resolver;
        $this->theme_resolver    = $theme_resolver;
    }

    public function create(Brand $brand)
    {
        return new BrandContainer($brand, $this->settings_resolver->getBrandSettings($brand), $this->theme_resolver);
    }
}
