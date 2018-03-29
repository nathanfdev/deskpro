<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Routing;

use Symfony\Component\Routing\RouteCollection as BaseRouteCollection;

class RouteCollection extends \Symfony\Component\Routing\RouteCollection
{
    /**
     * Array of operations to be run when this collection is resolved onto another collection.
     *
     * @var array
     */
    private $ops = [];

    /**
     * @param string $name
     * @param array  $info
     *
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
        $this->ops[] = ['rewriteController', [$find_controller, $replace_controller]];

        $find_controller    = trim($find_controller, ':').':';
        $replace_controller = trim($replace_controller, ':').':';

        foreach ($this as $route) {
            $ctrl = $route->getDefault('_controller');
            if (strpos($ctrl, $find_controller) === 0) {
                $ctrl = str_replace($find_controller, $replace_controller, $ctrl);
                $route->setDefault('_controller', $ctrl);
            }
        }
    }

    /**
     * Remove all routes for a given controller.
     *
     * @param string $find_controller
     */
    public function removeController($find_controller)
    {
        $this->ops[] = ['removeController', [$find_controller]];

        $find_controller = trim($find_controller, ':').':';
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
     * @param string|array $name... A name or array of names or multiple arguments of the same
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
            $this->ops[] = ['removeRoutes', [$name]];
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
        if ($collection instanceof self) {
            foreach ($collection->getMutateOps() as $info) {
                call_user_func_array([$this, $info[0]], $info[1]);
            }
        }

        parent::addCollection($collection);
    }
}
