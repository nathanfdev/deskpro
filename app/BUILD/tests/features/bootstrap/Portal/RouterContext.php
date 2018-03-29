<?php

/**
 * DeskPRO.
 */

namespace DpBehat\Portal;

use Behat\Gherkin\Node\TableNode;
use DeskPRO\Bundle\PortalBundle\Routing\PortalRouter;

class RouterContext extends BasePortalContext
{
    private $generated;

    /**
     * @When I generate a url for :route
     */
    public function iGenerateAUrlFor($route)
    {
        $this->generated = $this->getRouter()->generate($route);
    }

    /**
     * @Then the generated url should be :url
     */
    public function theGeneratedUrlShouldBe($url)
    {
        expect($this->generated)->toBe($url);
    }

    /**
     * @When I generate urls for:
     */
    public function iGenerateUrlsFor(TableNode $table)
    {
        $this->generated = [];

        foreach ($table->getColumnsHash() as $col) {
            parse_str($col['params'], $params);
            $this->generated[] = $this->getRouter()->generate($col['route'], $params ?: []);
        }
    }

    /**
     * @Then none of the generated urls should start with :start
     */
    public function noneOfTheGeneratedUrlsShouldStartWith($start)
    {
        foreach ($this->generated as $uri) {
            if (0 === strpos($uri, $start)) {
                throw new \Exception(sprintf('"%s" starts with "%s"', $uri, $start));
            }
        }
    }

    /**
     * @return PortalRouter
     */
    public function getRouter()
    {
        return $this->get('router');
    }
}
