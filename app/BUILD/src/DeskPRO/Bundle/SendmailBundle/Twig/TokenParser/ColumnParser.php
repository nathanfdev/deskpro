<?php

namespace DeskPRO\Bundle\SendmailBundle\Twig\TokenParser;

use DeskPRO\Bundle\SendmailBundle\Twig\Node\ColumnNode;
use Twig_Token;

class ColumnParser extends \Twig_TokenParser
{
    public function parse(Twig_Token $token)
    {
        $parser = $this->parser;
        $stream = $parser->getStream();

        $small     = null;
        $large     = null;
        $className = null;
        while (!$stream->test(Twig_Token::BLOCK_END_TYPE)) {
            if ($stream->test(Twig_Token::NAME_TYPE, 'small')) {
                $stream->next();
                $stream->expect(Twig_Token::OPERATOR_TYPE, '=');
                $small = $this->parser->getExpressionParser()->parseExpression();
            }
            if ($stream->test(Twig_Token::NAME_TYPE, 'large')) {
                $stream->next();
                $stream->expect(Twig_Token::OPERATOR_TYPE, '=');
                $large = $this->parser->getExpressionParser()->parseExpression();
            }
            if ($stream->test(Twig_Token::NAME_TYPE, 'class')) {
                $stream->next();
                $stream->expect(Twig_Token::OPERATOR_TYPE, '=');
                $className = $this->parser->getExpressionParser()->parseExpression();
            }
        }

        $stream->expect(Twig_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideColumnEnd'], true);
        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        $nodes['body'] = $body;
        if ($small) {
            $nodes['small'] = $small;
        }
        if ($large) {
            $nodes['large'] = $large;
        }
        if ($className) {
            $nodes['className'] = $className;
        }

        return new ColumnNode(
            $nodes,
            [],
            $token->getLine(),
            $this->getTag()
        );
    }

    public function decideColumnEnd(Twig_Token $token)
    {
        return $token->test('endcolumns');
    }

    public function getTag()
    {
        return 'columns';
    }
}
