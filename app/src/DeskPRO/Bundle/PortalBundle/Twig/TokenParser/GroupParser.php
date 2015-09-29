<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

        $nodes = array();
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
            array(),
            $token->getLine(),
            $this->getTag()
        );
    }

    public function getTag()
    {
        return 'group';
    }
}
