<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Form\DataTransformer;

use Orb\Input\Cleaner\Cleaner;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class HtmlPurifierTransformer.
 */
class HtmlPurifierTransformer implements DataTransformerInterface
{
    /**
     * @var string
     */
    private $cleaner;

    /**
     * @var string
     */
    private $html_type;

    /**
     * Constructor.
     *
     * @param Cleaner $cleaner
     * @param string  $type
     */
    public function __construct(Cleaner $cleaner, $type = 'html')
    {
        $this->cleaner   = $cleaner;
        $this->html_type = $type;

        if (strpos($type, 'html') === false) {
            throw new \InvalidArgumentException('$type must contains `html` word.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if ($value === null || empty($value) || ctype_digit($value)) {
            return $value;
        }
        if (!is_scalar($value)) {
            throw new TransformationFailedException('Expected scalar');
        }

        return $this->cleaner->clean($value, $this->html_type);
    }
}
