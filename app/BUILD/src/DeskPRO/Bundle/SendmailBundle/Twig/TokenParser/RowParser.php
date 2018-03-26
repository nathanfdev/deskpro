<?php

namespace DeskPRO\Bundle\SendmailBundle\Twig\TokenParser;

use DeskPRO\Bundle\SendmailBundle\Twig\Node\RowNode;
use Twig_Token;

class RowParser extends \Twig_TokenParser
{
    public function parse(Twig_Token $token)
    {
        $parser = $this->parser;
        $stream = $parser->getStream();

        $class = null;
        if ($stream->nextIf(Twig_Token::NAME_TYPE, 'class')) {
            $stream->expect(Twig_Token::OPERATOR_TYPE, '=');
            $class = $this->parser->getExpressionParser()->parseExpression();
        }

        $stream->expect(Twig_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideRowEnd'], true);
        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        $nodes['body'] = $body;
        if ($class) {
            $nodes['class'] = $class;
        }

        return new RowNode(
            $nodes,
            [],
            $token->getLine(),
            $this->getTag()
        );
    }

    public function decideRowEnd(Twig_Token $token)
    {
        return $token->test('endrow');
    }

    public function getTag()
    {
        return 'row';
    }
}
