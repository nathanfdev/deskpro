<?php

namespace DeskPRO\Bundle\AppBundle\ObjectAlias;

class QualifiedName
{
    /** @var string */
    private $localName;

    /** @var array  */
    private $qualifiers;

    const QUALIFIER_SEPARATOR = ':';

    /**
     * @param QualifiedName $name
     * @return bool
     */
    public static function isValidIdentifier(QualifiedName $name)
    {
        $patternHead = '#^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*#';
        $patternTail = '#[a-zA-Z0-9_\x7f-\xff]+#';

        $names = $name->toList();
        $valid = 1 === preg_match($patternHead, $names[0]);

        for ($i = 1; $i < count($names) && $valid === true; $i++ ) {
            $valid = 1 === preg_match($patternTail, $names[$i]);
        }

        return $valid;
    }

    /**
     * @param string $localName
     * @param array|string[] $qualifiers
     */
    public function __construct($localName, array $qualifiers)
    {
        $this->localName = $localName;
        $this->qualifiers = $qualifiers;
    }

    /**
     * @return string
     */
    public function getLocalName()
    {
        return $this->localName;
    }

    /**
     * @return array|string[]
     */
    public function getQualifiers()
    {
        return $this->qualifiers;
    }

    public function hasQualifiers()
    {
        return !empty($this->qualifiers);
    }

    /**
     * @return array|string[]
     */
    public function toList()
    {
        return array_merge( $this->qualifiers, [$this->localName]);
    }
}

