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
 * DeskPRO.
 */

namespace DpBehat\Portal;

use Application\DeskPRO\ORM\EntityManager;
use Behat\Behat\Context\Context;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeFactory;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use DeskPRO\Bundle\PortalBundle\Theme\ThemeRepository;

class PortalBrandThemeContext extends BasePortalContext
{
    /**
     * @var BrandStack
     */
    private $brand_stack;

    /**
     * @var ThemeRepository
     */
    private $theme_repository;
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(
        BrandStack $brand_stack,
        ThemeRepository $theme_repo,
        EntityManager $em
    ) {
        $this->brand_stack = $brand_stack;
        $this->theme_repository = $theme_repo;
        $this->em = $em;
    }

    /**
     * @Given the default brand is using the :theme_id theme
     */
    public function theActiveBrandHasTheme($theme_id)
    {
        $brand = $this->brand_stack->getDefault();
        $brand->setThemeId($theme_id);
        $this->em->persist($brand);
        $this->em->flush($brand);
    }
}
