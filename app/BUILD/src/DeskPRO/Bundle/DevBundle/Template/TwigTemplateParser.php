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
            $tokens = [];

            try {
                $stream = $this->twig->tokenize(file_get_contents($filepath));
                while ($token = $stream->next()) {
                    $tokens[$token->getType()][] = $token->getValue();
                }
            } catch (SyntaxError $e) {
            }

            $onParsed($tokens, $file, $filepath);
        }
    }
}
