<?php

namespace DeskPRO\Bundle\SendmailBundle\Twig\TokenParser;

use DeskPRO\Bundle\SendmailBundle\Twig\Node\ContainerNode;
use Twig_Token;

class ContainerParser extends \Twig_TokenParser
{
    public function parse(Twig_Token $token)
    {
        $parser = $this->parser;
        $stream = $parser->getStream();

        $body = '';
        if ($stream->nextIf(Twig_Token::BLOCK_END_TYPE)) {
            $body = $this->parser->subparse([$this, 'decideContainerEnd'], true);
        }
        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        $nodes['body'] = $body;

        return new ContainerNode(
            $nodes,
            [],
            $token->getLine(),
            $this->getTag()
        );
    }

    public function decideContainerEnd(Twig_Token $token)
    {
        return $token->test('endcontainer');
    }

    public function getTag()
    {
        return 'container';
    }
}
