<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Cutter\Def;

interface QuoteDef
{
    /**
     * Cut out the quote block.
     *
     * @param string $body
     * @param bool   $is_html
     *
     * @return string
     */
    public function cutQuoteBlock($body, $is_html = false);
}
