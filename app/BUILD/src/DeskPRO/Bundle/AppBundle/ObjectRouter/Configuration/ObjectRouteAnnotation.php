<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\ObjectRouter\Configuration;

abstract class ObjectRouteAnnotation extends ObjectLinkAnnotation
{
    /**
      * @var string
      */
     public $value;

     /**
      * @var array
      */
     public $route_param_map = [];

    public function __construct(array $values)
    {
        $this->setRouteName($values['value']);
        $this->setRouteParamMap(isset($values['route_param_map']) ? $values['route_param_map'] : null);
        parent::__construct($values);
    }

    public function toRouteArray()
    {
        return [
             'route'           => $this->getRouteName(),
             'route_param_map' => $this->getRouteParamMap(),
         ];
    }

     /**
      * @return string
      */
     public function getRouteName()
     {
         return $this->route_name;
     }

     /**
      * @param string $route_name
      */
     public function setRouteName($route_name)
     {
         $this->route_name = $route_name;
     }

     /**
      * @return array
      */
     public function getRouteParamMap()
     {
         return $this->route_param_map;
     }

     /**
      * @param array $route_param_map
      */
     public function setRouteParamMap($route_param_map)
     {
         $this->route_param_map = $route_param_map;
     }
}
