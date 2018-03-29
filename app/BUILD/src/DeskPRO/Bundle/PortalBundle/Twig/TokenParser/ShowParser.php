<?php

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
