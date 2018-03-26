<?php

namespace DeskPRO\Bundle\SendmailBundle\Twig\TokenParser;

use DeskPRO\Bundle\SendmailBundle\Twig\Node\WrapperNode;
use Twig_Token;

class WrapperParser extends \Twig_TokenParser
{
    public function parse(Twig_Token $token)
    {
        $parser = $this->parser;
        $stream = $parser->getStream();

        $class   = null;
        $bgColor = null;
        while (!$stream->test(Twig_Token::BLOCK_END_TYPE)) {
            if ($stream->test(Twig_Token::NAME_TYPE, 'class')) {
                $stream->next();
                $stream->expect(Twig_Token::OPERATOR_TYPE, '=');
                $class = $this->parser->getExpressionParser()->parseExpression();
            }
            if ($stream->test(Twig_Token::NAME_TYPE, 'bgcolor')) {
                $stream->next();
                $stream->expect(Twig_Token::OPERATOR_TYPE, '=');
                $bgColor = $this->parser->getExpressionParser()->parseExpression();
            }
        }

        $stream->expect(Twig_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideWrapperEnd'], true);
        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        $nodes['body'] = $body;
        if ($class) {
            $nodes['class'] = $class;
        }
        if ($bgColor) {
            $nodes['bgcolor'] = $bgColor;
        }

        return new WrapperNode(
            $nodes,
            [],
            $token->getLine(),
            $this->getTag()
        );
    }

    public function decideWrapperEnd(Twig_Token $token)
    {
        return $token->test('endwrapper');
    }

    public function getTag()
    {
        return 'wrapper';
    }
}
