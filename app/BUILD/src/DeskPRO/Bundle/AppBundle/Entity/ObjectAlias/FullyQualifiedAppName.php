<?php

namespace DeskPRO\Bundle\AppBundle\Entity\ObjectAlias;

use DeskPRO\Bundle\AppBundle\ObjectAlias;

class FullyQualifiedAppName
{
    /**
     * @param FullyQualifiedAppName $qualifier
     * @return array
     */
    public static function toArray( FullyQualifiedAppName $qualifier)
    {
        return ['app', $qualifier->getId()];
    }

    /**
     * @param array $qualifier
     * @return FullyQualifiedAppName|null
     */
    public static function parseArray( array $qualifier)
    {
        if (count($qualifier) === 3 && $qualifier[0] === 'app') {
            $id = (integer) $qualifier[1];
            if ($qualifier[1] === (string) $id && is_string($qualifier[2]) && !empty($qualifier[2]) ) {
                return new FullyQualifiedAppName($qualifier[1], $qualifier[2]);
            }
        }

        return null;
    }

    /**
     * @param ObjectAlias\QualifiedName $qualifiedName
     * @return FullyQualifiedAppName|null
     */
    public static function parseName( ObjectAlias\QualifiedName $qualifiedName)
    {
        return FullyQualifiedAppName::parseArray($qualifiedName->toList());
    }

    /**
     * @param string $id
     * @param string $localName
     */
    public function __construct($id, $localName)
    {
        $this->id = $id;
        $this->localName = $localName;
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLocalName()
    {
        return $this->localName;
    }

    /**
     * @param $other
     * @return bool
     */
    public function equals($other)
    {
        return $other instanceof FullyQualifiedAppName
            && $other->getId()  === $this->getId()
            && $other->getLocalName() === $this->getLocalName()
        ;
    }
}
