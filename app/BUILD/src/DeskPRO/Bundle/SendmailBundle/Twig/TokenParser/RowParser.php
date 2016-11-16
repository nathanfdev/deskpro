<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
            $class = $this->parser->getExpressionParser()->parseExpression();
        }

        $body = '';
        if ($stream->nextIf(Twig_Token::BLOCK_END_TYPE)) {
            $body = $this->parser->subparse([$this, 'decideRowEnd'], true);
        }
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
