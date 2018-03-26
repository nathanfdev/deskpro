<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Reports;

use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlCompiler;
use DeskPRO\Bundle\ReportBundle\Dpql2\Statement\SelectPart;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class DpqlPartsTransformer.
 */
class DpqlPartsTransformer implements DataTransformerInterface
{
    /**
     * @var DpqlCompiler
     */
    private $compiler;

    /**
     * Constructor.
     *
     * @param DpqlCompiler $compiler
     */
    public function __construct(DpqlCompiler $compiler)
    {
        $this->compiler = $compiler;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        try {
            return $this->compiler->compile($value)->getDpqlPartsForInput();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (is_array($value)) {
            return SelectPart::getQueryStringFromParts($value);
        }

        return '';
    }
}
