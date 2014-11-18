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

namespace Application\PortalBundle\Theme;

use Application\PortalBundle\Themes\Base\BaseTheme;
use Application\PortalBundle\Themes\Sidebar\SidebarTheme;
use Application\PortalBundle\Themes\Simple\SimpleTheme;
use Application\PortalBundle\Themes\Standard\StandardTheme;
use Application\PortalBundle\Themes\TabBar\TabBarTheme;

/**
 * A reporistory of themes.
 * I can see this someday being a service, but we hardcode for now since we have so few.
 *
 * @package Application\PortalBundle\Theme
 */
class ThemeRepository
{
    /**
     * @var ThemeInterface[]
     */
    private $themes;

    public function __construct()
    {
        $this->themes = array(
            $base = new BaseTheme(),
            new StandardTheme($base),
            new SimpleTheme($base),
            new SidebarTheme($base),
            new TabBarTheme($base),
        );
    }

    /**
     * Get a theme by its ID. ie. $repo->find('standard')
     *
     * @param $id
     * @return ThemeInterface
     */
    public function find($id)
    {
        foreach ($this->themes as $theme) {
            if ($id === $theme->getId()) {
                return $theme;
            }
        }

        return null;
    }

    /**
     * A list of all themes in the order they are registered
     *
     * @return ThemeInterface[]
     */
    public function findAll()
    {
        return $this->themes;
    }
}
