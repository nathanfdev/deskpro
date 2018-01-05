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

namespace DpBehat\Api;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behatch\Json\Json;
use DpBehat\Data\DataContext;

/**
 * Class JsonContext.
 */
class JsonContext extends \Behatch\Context\JsonContext
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

    /**
     * @override
     */
    public function theJsonNodeShouldBeNull($node)
    {
        $node = DataContext::replace($node);
        parent::theJsonNodeShouldBeNull($node);
    }

    /**
     * @override
     */
    public function theJsonNodeShouldBeEqualTo($node, $value)
    {
        $node = DataContext::replace($node);

        if (is_string($value)) {
            $value = DataContext::replace($value);
        }

        parent::theJsonNodeShouldBeEqualTo($node, $value);
    }

    /**
     * @override
     */
    public function theJsonNodeShouldBeEqualToTheString($node, $text)
    {
        $node = DataContext::replace($node);
        $text = str_replace('\n', "\n", $text);

        parent::theJsonNodeShouldBeEqualToTheString($node, $text);
    }

    /**
     * @override
     */
    public function theJsonNodeShouldContain($node, $text)
    {
        $node = DataContext::replace($node);
        $text = DataContext::replace($text);

        parent::theJsonNodeShouldContain($node, $text);
    }

    /**
     * Checks, that given JSON list does not contain given value
     *
     * @Then the JSON list node :node should not contain :text
     */
    public function theJsonListNodeShouldNotContain($node, $text)
    {
        $node = DataContext::replace($node);
        $text = DataContext::replace($text);


        $json = $this->getJson();
        $actual = $this->inspector->evaluate($json, $node);

        foreach ($actual as $item) {

            if ($text === (string) $item) {
                throw new \Exception(
                    sprintf('The node `%s` contains: %s', json_encode($actual), $text)
                );
            }
        }
    }

    /**
     * @override
     */
    public function theJsonNodeShouldNotContain($node, $text)
    {
        $node = DataContext::replace($node);
        $text = DataContext::replace($text);

        parent::theJsonNodeShouldNotContain($node, $text);
    }

    /**
     * @override
     */
    public function theJsonNodeShouldExist($name)
    {
        $name = DataContext::replace($name);
        parent::theJsonNodeShouldExist($name);
    }

    /**
     * @override
     */
    public function theJsonNodeShouldHaveElements($node, $count)
    {
        $node = DataContext::replace($node);
        parent::theJsonNodeShouldHaveElements($node, $count);
    }

    /**
     * This method is used to compare value w/o placeholders processing.
     *
     * @Then the JSON node :node should be equal to :text raw value
     */
    public function theJsonNodeShouldBeEqualToRawValue($node, $value)
    {
        parent::theJsonNodeShouldBeEqualTo($node, $value);
    }

    /**
     * Checks, that given JSON node is equal to given value
     *
     * @Then the JSON node :node should be equal to node:
     */
    public function theJsonNodeShouldBeEqualToNode($node, PyStringNode $text)
    {
        $json = $this->getJson();
        try {
            $node = DataContext::replace($node);
            $actual = $this->inspector->evaluate($json, $node);
            $actual = new Json(json_encode($actual));
        }
        catch (\Exception $e) {
            throw new \Exception('The actual JSON is not valid', 0, $e);
        }

        try {

            $expected = DataContext::replace($text->getRaw(), false);
            $expected = new Json($expected);
        }
        catch (\Exception $e) {
            throw new \Exception('The expected JSON is not valid', 0, $e);
        }


        $this->assertSame(
            (string) $expected,
            (string) $actual,
            "The json is equal to:\n". $expected->encode(true) . "\n" , $actual->encode(true)
        );
    }

    /**
     *
     * @Given I save the JSON node :node as placeholder :placeholder
     *
     * @param $node
     * @param $placeholder
     */
    public function iSaveTheJsonNodeAsPlaceholder($node, $placeholder)
    {
        $json   = $this->getJson();
        $actual = $this->inspector->evaluate($json, $node);

        DataContext::setPlaceholder($placeholder, $actual);
    }

}
