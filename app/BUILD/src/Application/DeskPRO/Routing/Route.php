<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Routing;

class Route extends \Symfony\Component\Routing\Route
{
    /**
     * @param $path
     * @param array $info
     *
     * @return Route
     */
    public static function create(array $info)
    {
        $route = new self($info['path']);

        if ($route) {
            $route->setFromArray($info);
        }

        return $route;
    }

    /**
     * @param array $info
     */
    public function setFromArray(array $info)
    {
        if (isset($info['defaults']) && $info['defaults']) {
            if (isset($info['controller'])) {
                $info['defaults']['_controller'] = $info['controller'];
            }
            $this->setDefaults($info['defaults']);
        } elseif (isset($info['controller'])) {
            $this->setDefaults(['_controller' => $info['controller']]);
        }

        if (isset($info['requirements']) && $info['requirements']) {
            $this->setRequirements($info['requirements']);
        }
        if (isset($info['options']) && $info['options']) {
            $this->setOptions($info['options']);
        }
        if (isset($info['host']) && $info['host']) {
            $this->setHost($info['host']);
        }
        if (isset($info['schemes']) && $info['schemes']) {
            $this->setSchemes($info['schemes']);
        }
        if (isset($info['methods']) && $info['methods']) {
            $this->setMethods($info['methods']);
        }
    }
}
