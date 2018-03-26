<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Twig\TokenParser;

use DeskPRO\Bundle\PortalBundle\Twig\Node\GroupNode;
use Twig_Token;

/**
 * A group surrounds multiple items, which then lets you show/hide the entire group
 * based on criteria. The simlest case is to only show the group when X number
 * of inner items are visible, but you can get specific like testing for specific
 * items or any other arbitrary criteria.
 *
 * Summary:
 *
 * Group: {% group %}...{% endgroup %}
 * Group with min (defaults to 1 if unspecified): {% group min 3 %}...{% endgroup %}
 * Group with if: {% group if my_condition %}...{% endgroup %}
 * Group with min and if: {% group min 2 if my_condition %}...{% endgroup %}
 *
 * Examples:
 * <code>
 * {# By default, the group only shows when at least one item is visible #}
 * {% group %}
 *     <div class="my-wrapper">
 *         {% item if something %}
 *         ...
 *         {% enditem %}
 *     </div>
 * {% endgroup %}
 *
 * {# Useful for something like colums #}
 * {# Note: min syntax defines how many items must be visible #}
 * {% group min 3 %}
 *     {% item if something %}
 *         <div class="column">...</div>
 *     {% enditem %}
 *     {% item if something %}
 *         <div class="column">...</div>
 *     {% enditem %}
 *     {% item if something %}
 *         <div class="column">...</div>
 *     {% enditem %}
 * {% endgroup %}
 *
 * {# Use the group variable for things like counts #}
 * {% group min 3 %}
 *     <div class="columns cols-{{group.count}}">
 *         {% item if something %}
 *             <div class="column">...</div>
 *         {% enditem %}
 *         {% item if something %}
 *             <div class="column">...</div>
 *         {% enditem %}
 *         {% item if something %}
 *             <div class="column">...</div>
 *         {% enditem %}
 *     </div>
 * {% endgroup %}
 *
 * {# Groups can have arbitrary criteria (saves a wrapper if tag) #}
 * {% group min 3 if some_cond %}{% endgroup %}
 *
 * {# You can use named items to reference specifics in the criteria #}
 * {% group if group.items.my_item  %}
 *     {% item my_item if something %}
 *         <div class="column">...</div>
 *     {% enditem %}
 *     {% item %}
 *         <div class="column">...</div>
 *     {% enditem %}
 *     {% item if something %}
 *         <div class="column">...</div>
 *     {% enditem %}
 * {% endgroup %}
 * </code>
 */
class GroupParser extends \Twig_TokenParser
{
    public function parse(Twig_Token $token)
    {
        $parser = $this->parser;
        $stream = $parser->getStream();

        $min = null;
        if ($stream->nextIf(Twig_Token::NAME_TYPE, 'min')) {
            $min = $this->parser->getExpressionParser()->parseExpression();
        }

        $condition = null;
        if ($stream->nextIf(Twig_Token::NAME_TYPE, 'if')) {
            $condition = $this->parser->getExpressionParser()->parseExpression();
        }

        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        $values = $this->parser->subparse(function (Twig_Token $token) {
            return $token->test('endgroup');
        }, true);
        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        $nodes = [];
        if ($min) {
            $nodes['min'] = $min;
        }
        if ($condition) {
            $nodes['condition'] = $condition;
        }
        if ($values) {
            $nodes['values'] = $values;
        }

        return new GroupNode(
            $nodes,
            [],
            $token->getLine(),
            $this->getTag()
        );
    }

    public function getTag()
    {
        return 'group';
    }
}
