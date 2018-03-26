<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Expression;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ParserCache\ParserCacheInterface;

/**
 * Class TermEngineExpressionLanguage.
 */
class TermEngineExpressionLanguage extends ExpressionLanguage
{
    /**
     * {@inheritdoc}
     */
    public function __construct(ParserCacheInterface $cache = null, array $providers = [])
    {
        array_unshift($providers, new TermEngineExpressionProvider());

        parent::__construct($cache, $providers);
    }
}
