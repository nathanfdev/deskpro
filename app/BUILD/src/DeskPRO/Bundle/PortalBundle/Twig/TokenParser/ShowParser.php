<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

use DeskPRO\Bundle\PortalBundle\Twig\Node\ShowNode;
use Twig_Token;

/**
 * Show a tag using context of current page: {% show some_tag %}
 * Show a tag without using context:         {% show section some_tag %}
 * Show a tag with context and with vars:    {% show some_tag with {vars} %}
 * Show a tag without context and with vars: {% show section some_tag with {vars} %}.
 */
class ShowParser extends \Twig_TokenParser
{
    /**
     * @var \Twig_Extension
     */
    private $ext;

    /**
     * @param \Twig_Extension $ext
     */
    public function __construct(\Twig_Extension $ext)
    {
        $this->ext = $ext;
    }

    public function parse(Twig_Token $token)
    {
        $parser = $this->parser;
        $stream = $parser->getStream();

        $this_page = true;
        if ($stream->nextIf(Twig_Token::NAME_TYPE, 'section')) {
            $this_page = false;
        }

        $name = $stream->expect(Twig_Token::NAME_TYPE)->getValue();

        $variables = null;
        if ($stream->nextIf(Twig_Token::NAME_TYPE, 'with')) {
            $variables = $this->parser->getExpressionParser()->parseExpression();
        }

        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        return new ShowNode(
            $variables ? ['variables' => $variables] : [],
            [
                'tag_name'    => $name,
                'is_page_tag' => $this_page,
                'ext_name'    => $this->ext->getName(),
            ],
            $token->getLine(),
            $this->getTag()
        );
    }

    public function getTag()
    {
        return 'show';
    }
}
