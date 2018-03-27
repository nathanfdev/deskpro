<?php

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
