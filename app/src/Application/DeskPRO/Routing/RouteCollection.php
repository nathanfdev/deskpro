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
 */

namespace Application\DeskPRO\Routing;

use \Symfony\Component\Routing\RouteCollection as BaseRouteCollection;

class RouteCollection extends \Symfony\Component\Routing\RouteCollection
{
    /**
     * Array of operations to be run when this collection is resolved onto another collection
     *
     * @var array
     */
    private $ops = array();

    /**
     * @param  string $name
     * @param  array  $info
     * @return Route
     */
    public function create($name, array $info)
    {
        $route = Route::create($info);
        $this->add($name, $route);

        return $route;
    }

    /**
     * Rewrites all existing routes with a given controller to use a new controller
     * instead.
     *
     * Used mainly in cloud routing to rewrite routes to use a Cloud controller
     * which overrides behaviour.
     *
     * @param string $find_controller
     * @param string $replace_controller
     */
    public function rewriteController($find_controller, $replace_controller)
    {
        $this->ops[] = array('rewriteController', array($find_controller, $replace_controller));

        $find_controller    = trim($find_controller, ':') . ':';
        $replace_controller = trim($replace_controller, ':') . ':';

        foreach ($this as $route) {
            $ctrl = $route->getDefault('_controller');
            if (strpos($ctrl, $find_controller) === 0) {
                $ctrl = str_replace($find_controller, $replace_controller, $ctrl);
                $route->setDefault('_controller', $ctrl);
            }
        }
    }


    /**
     * Remove all routes for a given controller
     *
     * @param string $find_controller
     */
    public function removeController($find_controller)
    {
        $this->ops[] = array('removeController', array($find_controller));

        $find_controller = trim($find_controller, ':') . ':';
        foreach ($this as $name => $route) {
            $ctrl = $route->getDefault('_controller');
            if (strpos($ctrl, $find_controller) === 0) {
                $this->remove($name);
            }
        }
    }


    /**
     * Removes an existing route $name.
     *
     * Used mainly in cloud routing to disable routes that dont apply.
     *
     * @param  string|array $name... A name or array of names or multiple arguments of the same
     * @return void
     */
    public function removeRoutes($name)
    {
        if (func_num_args() != 1) {
            $args = func_get_args();
            foreach ($args as $a) {
                $this->removeRoutes($a);
            }
        } elseif (is_array($name)) {
            foreach ($name as $a) {
                $this->removeRoutes($a);
            }
        } else {
            $this->remove($name);
            $this->ops[] = array('removeRoutes', array($name));
        }
    }

    /**
     * @return array
     */
    public function getMutateOps()
    {
        return $this->ops;
    }

    /**
     * @param BaseRouteCollection $collection
     */
    public function addCollection(BaseRouteCollection $collection)
    {
        // If this is a RouteCollection then we need to run the
        // remove* and rewriteController ops on the existing collection
        if ($collection instanceof RouteCollection) {
            foreach ($collection->getMutateOps() as $info) {
                call_user_func_array(array($this, $info[0]), $info[1]);
            }
        }

        parent::addCollection($collection);
    }
}
