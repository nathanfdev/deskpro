<?php

/**
 * DeskPRO.
 */

namespace DpBehat\Portal;

use DpBehat\BaseContext;

/**
 * Class RefsContext.
 */
class RefsContext extends BaseContext
{
    /**
     * @When I visit ":url"
     */
    public function iVisit($url)
    {
        $this->visitPath($this->replaceRefs($url));
    }

    /**
     * @param string $url
     *
     * @return string
     */
    private function replaceRefs($url)
    {
        $callback = function ($matches) {
            if (array_key_exists($matches[1], TicketContext::$tickets)) {
                return TicketContext::$tickets[$matches[1]]->getId();
            } else {
                throw new \Exception("Ref {$matches[1]} isn't defined");
            }
        };
        $url = preg_replace_callback('/\{(.+)\}/', $callback, $url);

        echo $url;

        return $url;
    }
}
