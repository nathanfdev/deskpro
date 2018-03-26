<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\ObjectRouter\Configuration;

use DeskPRO\Bundle\AppBundle\ObjectRouter\LinkConfigAnnotationRepo;

abstract class ObjectLinkAnnotation
{
    /**
      * @var string
      */
     public $type;

    public function __construct(array $values = [])
    {
        $this->setType(isset($values['type']) ? $values['type'] : null);
    }

     /**
      * @return string
      */
     public function getType()
     {
         return $this->type ?: LinkConfigAnnotationRepo::INTERNAL_DEFAULT;
     }

     /**
      * @param string $type
      */
     public function setType($type)
     {
         $this->type = $type;
     }
}
