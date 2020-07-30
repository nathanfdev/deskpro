<?php

namespace DeskPRO\Bundle\DevBundle\Template;

use Symfony\Component\Finder\Finder;
use Twig\Environment;
use Twig\Error\SyntaxError;

/**
 * Class TwigTemplateParser
 *
 * @package DeskPRO\Bundle\DevBundle\Template
 */
class TwigTemplateParser
{
    /**
     * @var Environment
     */
    private $twig;

    /**
     * TwigTemplateParser constructor.
     *
     * @param Environment $twig
     */
    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param Finder   $finder
     * @param callable $onToken Callable with @see \Twig\Token, filepath path and \SplFileInfo as arguments
     * @throws SyntaxError
     */
    public function parse(Finder $finder, callable $onParsed)
    {
        /** @var \SplFileInfo $file */
        foreach ($finder as $filepath => $file) {
            $onParsed($this->parseFromString(file_get_contents($filepath)), $file, $filepath);
        }
    }

    /**
     * @param string $template
     * @return array List of Twig template token types and values
     */
    public function parseFromString($template, array $onlyTypes = null)
    {
        $tokens = [];

        try {
            $stream = $this->twig->tokenize($template);
            while ($token = $stream->next()) {
                if ($onlyTypes && in_array($token->getType(), $onlyTypes)) {
                    $tokens[$token->getType()][] = $token->getValue();
                } elseif (!$onlyTypes) {
                    $tokens[$token->getType()][] = $token->getValue();
                }
            }
        } catch (SyntaxError $e) {
        }

        return $tokens;
    }
}
