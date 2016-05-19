<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
namespace DpBehat\Api;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use DpBehat\BaseContext;

/**
 * Class JsonContext.
 */
class JsonContext extends BaseContext
{
    /**
     * @var RestContext
     */
    private $restContext;

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment       = $scope->getEnvironment();
        $this->restContext = $environment->getContext('DpBehat\Api\RestContext');
    }

    /**
     * @Then the JSON node ":node" should have elements sorted by the ":prop" property in ":order" order
     */
    public function theJsonNodeShouldHaveElementsSorted($node, $prop, $order)
    {
        $json = $this->restContext->getSession()->getPage()->getContent();
        if (!$data = json_decode($json, true)) {
            throw new \Exception('The last response is not a valid JSON');
        }
        if (!array_key_exists($node, $data)) {
            throw new \Exception("$node doesn't exist");
        }
        if (!is_array($data[$node])) {
            throw new \Exception("$node isn't an array");
        }

        $elements = $data[$node];
        $length   = count($elements);
        for ($i = 0; $i < $length - 1; ++$i) {
            \PHPUnit_Framework_Assert::assertArrayHasKey($prop, $elements[$i]);
            \PHPUnit_Framework_Assert::assertArrayHasKey($prop, $elements[$i + 1]);

            if ($order === 'asc') {
                \PHPUnit_Framework_Assert::assertGreaterThanOrEqual($elements[$i][$prop], $elements[$i + 1][$prop]);
            } elseif ($order === 'desc') {
                \PHPUnit_Framework_Assert::assertLessThanOrEqual($elements[$i][$prop], $elements[$i + 1][$prop]);
            } else {
                throw new \Exception("Unknown `$order` ordering, order must be desc or asc");
            }
        }
    }
}
