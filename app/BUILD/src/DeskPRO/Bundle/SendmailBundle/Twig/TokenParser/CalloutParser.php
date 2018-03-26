<?php

namespace DeskPRO\Bundle\SendmailBundle\Twig\TokenParser;

use DeskPRO\Bundle\SendmailBundle\Twig\Node\CalloutNode;
use Twig_Token;

class CalloutParser extends \Twig_TokenParser
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

        $body = '';
        if ($stream->nextIf(Twig_Token::BLOCK_END_TYPE)) {
            $body = $this->parser->subparse([$this, 'decideBlockEnd'], true);
        }
        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        $nodes['body'] = $body;
        if ($class) {
            $nodes['class'] = $class;
        }

        return new CalloutNode(
            $nodes,
            [],
            $token->getLine(),
            $this->getTag()
        );
    }

    public function decideBlockEnd(Twig_Token $token)
    {
        return $token->test('endcallout');
    }

    public function getTag()
    {
        return 'callout';
    }
}
