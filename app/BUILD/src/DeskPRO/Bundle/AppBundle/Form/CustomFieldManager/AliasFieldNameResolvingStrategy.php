<?php

namespace DeskPRO\Bundle\AppBundle\Form\CustomFieldManager;

use DeskPRO\Bundle\AppBundle\ObjectAlias;
use Doctrine\ORM\EntityManager;

class AliasFieldNameResolvingStrategy implements FieldNameResolvingStrategy
{
    /**
     * @var ObjectAlias\IdFinder
     */
    private $idFinder;

    public function __construct(ObjectAlias\IdFinder $finder)
    {
        $this->idFinder = $finder;
    }

    /**
     * @param string|$fieldName
     * @return string|null
     */
    public function resolve($fieldName)
    {
        $pattern = '#^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*#';
        if (1 === preg_match($pattern, $fieldName, $matches)) {
            $match = $matches[0];
            return $this->idFinder->findId($match);
        }

        return null;
    }
}
