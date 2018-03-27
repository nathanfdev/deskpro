<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Form\DataTransformer;

use DeskPRO\Bundle\AppBundle\TermEngine\Exception\TermTypeDoesNotExistException;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\Util\TermToJsonConverter;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class TermEngineTermTransformer.
 */
class TermEngineTermTransformer implements DataTransformerInterface
{
    /**
     * @var TermToJsonConverter
     */
    private $converter;

    /**
     * Constructor.
     *
     * @param TermToJsonConverter $converter
     */
    public function __construct(TermToJsonConverter $converter)
    {
        $this->converter = $converter;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (!$value instanceof TermInterface) {
            return [
                'type'    => '',
                'op'      => '',
                'options' => [],
            ];
        }

        return $this->converter->termToArray($value);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (!$value) {
            return false;
        }
        if ($value instanceof TermInterface) {
            return $value;
        }

        try {
            return $this->converter->arrayToTerm($value);
        } catch (TermTypeDoesNotExistException $e) {
            throw new TransformationFailedException('Unable to get term with code "'.$e->getMessage().'".');
        }
    }
}
