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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Twig\TokenParser;

use DeskPRO\Bundle\PortalBundle\Twig\Node\GroupItemNode;
use Twig_Token;

/**
 * An item is part of a group. An item out of a group makes no sense.
 *
 * {% item if some_condition %}{% enditem %}
 * {% item my_id %}{% enditem %}
 * {% item my_id if some_condition %} {%endif %}
 *
 * Giving an item an ID makes it possible to use the value in `group.items.my_id`, useful
 * when you want to use it as part of a condition.
 *
 * @see GroupParser
 */
class GroupItemParser extends \Twig_TokenParser
{
    public function parse(Twig_Token $token)
    {
        $parser = $this->parser;
        $stream = $parser->getStream();

        $condition = null;
        $name      = $stream->nextIf(Twig_Token::NAME_TYPE);
        if ($name && $name->getValue() == 'if') {
            $name      = null;
            $condition = $this->parser->getExpressionParser()->parseExpression();
        } else {
            if ($stream->nextIf(Twig_Token::NAME_TYPE, 'if')) {
                $condition = $this->parser->getExpressionParser()->parseExpression();
            }
        }

        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        $values = $this->parser->subparse(function (Twig_Token $token) {
            return $token->test('enditem');
        }, true);
        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        $nodes = [];
        if ($condition) {
            $nodes['condition'] = $condition;
        }
        $nodes['values'] = $values;

        return new GroupItemNode(
            $nodes,
            ['item_name' => $name ? $name->getValue() : null],
            $token->getLine(),
            $this->getTag()
        );
    }

    public function getTag()
    {
        return 'item';
    }
}
