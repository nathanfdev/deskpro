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
        $this->generated = array();

        foreach ($table->getColumnsHash() as $col) {
            parse_str($col['params'], $params);
            $this->generated[] = $this->getRouter()->generate($col['route'], $params ?: array());
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
