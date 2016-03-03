<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\ApiBundle\ApiDoc\Extractor;

use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Component\Util\TypeUtils;
use Nelmio\ApiDocBundle\Extractor\ApiDocExtractor as BaseApiDocExtractor;
use Symfony\Component\Routing\Route;

class ApiDocExtractor extends BaseApiDocExtractor
{
    protected $action_list = [
        'list',
        'get',
        'post',
        'put',
        'delete',
    ];

    /**
     * @return Route[]
     */
    public function getRoutes()
    {
        return array_filter($this->router->getRouteCollection()->all(), function (Route $r) {
            $ctrl = $r->getDefault('_controller');
            $parts = explode('::', $ctrl);
            $is_controller = preg_match('#^DeskPRO\\\\Bundle\\\\ApiBundle\\\\#', $ctrl);

            $exposed = true;
            if ($is_controller && $parts[0]) {
                $reflection = new \ReflectionClass($parts[0]);
                $action = TypeUtils::cleanAction($ctrl);
                if (
                    $reflection->isSubclassOf(CrudController::class)
                    && in_array($action, $this->action_list)
                    && $expose = $reflection->getProperty('exposeOnly')->getValue()
                ) {
                    $exposed = in_array(TypeUtils::cleanAction($ctrl), $expose);
                }
            }

            return $ctrl && $is_controller && $exposed;
        });
    }
}
