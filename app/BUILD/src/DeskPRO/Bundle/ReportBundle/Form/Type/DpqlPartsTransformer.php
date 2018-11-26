<?php

namespace DeskPRO\Bundle\ReportBundle\Form\Type;

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
     * @var array
     */
    private $variables;

    /**
     * Constructor.
     *
     * @param DpqlCompiler $compiler
     * @param array        $variables
     */
    public function __construct(DpqlCompiler $compiler, array $variables)
    {
        $this->compiler  = $compiler;
        $this->variables = $variables;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        try {
            return $this->compiler->compile($value, ['variables' => $this->variables])->getDpqlPartsForInput();
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
