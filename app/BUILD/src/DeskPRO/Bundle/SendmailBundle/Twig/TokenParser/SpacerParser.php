<?php

namespace DeskPRO\Bundle\SendmailBundle\Twig\TokenParser;

use DeskPRO\Bundle\SendmailBundle\Twig\Node\SpacerNode;
use Twig_Node_Print;
use Twig_Token;

class SpacerParser extends \Twig_TokenParser
{
    public function parse(Twig_Token $token)
    {
        $parser = $this->parser;
        $stream = $parser->getStream();

        $size = null;
        if ($stream->nextIf(Twig_Token::NAME_TYPE, 'size')) {
            $stream->expect(Twig_Token::OPERATOR_TYPE, '=');
            $size = $this->parser->getExpressionParser()->parseExpression();
        }
        $stream->expect(Twig_Token::BLOCK_END_TYPE);
        $this->parser->subparse([$this, 'decideSpacerEnd'], true);
        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        $nodes = [];
        if ($size) {
            $nodes['size'] = new Twig_Node_Print($size, 1);
        }

        return new SpacerNode(
            $nodes,
            [],
            $token->getLine(),
            $this->getTag()
        );
    }

    public function decideSpacerEnd(Twig_Token $token)
    {
        return $token->test('endspacer');
    }

    public function getTag()
    {
        return 'spacer';
    }
}
