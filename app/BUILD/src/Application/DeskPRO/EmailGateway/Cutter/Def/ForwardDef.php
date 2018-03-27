<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Cutter\Def;

interface ForwardDef
{
    /**
     * Get an array of info from the forwarded block.
     *
     * @param string $body
     * @param bool   $is_html
     *
     * @return array
     */
    public function getForwardInfo($body, $is_html = false);
}
