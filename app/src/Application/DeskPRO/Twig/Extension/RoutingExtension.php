<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * @category Templating
 */

namespace Application\DeskPRO\Twig\Extension;

use Symfony\Bridge\Twig\Extension\RoutingExtension as BaseRoutingExtension;

class RoutingExtension extends BaseRoutingExtension
{
    /**
     * Custom getPath to eat exception when not in debug mode.
     *
     * This is because people can screw up their site if they edit templates and then try to render
     * a malformed link. In that scenario, better to not fatal error.
     *
     * @param  string     $name
     * @param  array      $parameters
     * @param  bool       $relative
     * @return string
     * @throws \Exception
     */
    public function getPath($name, $parameters = array(), $relative = false)
    {
        try {
            return parent::getPath($name, $parameters, $relative);
        } catch (\Exception $e) {
            if (isset($GLOBALS['DP_CONFIG']['debug']['dev']) && $GLOBALS['DP_CONFIG']['debug']['dev']) {
                throw $e;
            }

            return '';
        }
    }
}
