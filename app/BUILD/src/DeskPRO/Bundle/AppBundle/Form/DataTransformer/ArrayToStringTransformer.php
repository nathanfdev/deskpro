<?php

namespace DeskPRO\Bundle\AppBundle\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class ArrayToStringTransformer.
 */
class ArrayToStringTransformer implements DataTransformerInterface
{
    /**
     * @var string
     */
    private $delimiter;

    /**
     * Constructor.
     *
     * @param string $delimiter
     */
    public function __construct($delimiter = ',')
    {
        $this->delimiter = $delimiter;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if ($value === null) {
            return '';
        }
        if ($value instanceof ArrayCollection) {
            $value = $value->toArray();
        }

        return implode($this->delimiter, (array) $value);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        $val = explode($this->delimiter, $value);
        $k   = [];

        foreach ($val as $v) {
            $k[] = trim((string) $v);
        }

        return $k;
    }
}
