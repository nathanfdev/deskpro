<?php

namespace DpBehat\Portal;

use Behat\Mink\Exception\ExpectationException;
use DpBehat\Data\DataContext;

/**
 * Class MinkContext.
 */
class MinkContext extends \Behat\MinkExtension\Context\MinkContext
{
    /**
     * @override
     *
     * @param string $page
     */
    public function visit($page)
    {
        parent::visit(DataContext::replace($page));
    }

    /**
     * @override
     *
     * @param string $text
     */
    public function assertResponseContains($text)
    {
        parent::assertResponseContains(DataContext::replace($text));
    }

    /**
     * @override
     *
     * @param string $text
     */
    public function assertResponseNotContains($text)
    {
        parent::assertResponseNotContains(DataContext::replace($text));
    }

    /**
     * @override
     *
     * @param string $name
     * @param string $value
     */
    public function assertFieldContains($name, $value)
    {
        parent::assertFieldContains(DataContext::replace($name), DataContext::replace($value));
    }

    /**
     * @override
     *
     * @param $checkbox
     */
    public function assertCheckboxChecked($checkbox)
    {
        parent::assertCheckboxChecked(DataContext::replace($checkbox));
    }

    /**
     * @override
     *
     * @param $checkbox
     */
    public function assertCheckboxNotChecked($checkbox)
    {
        parent::assertCheckboxNotChecked(DataContext::replace($checkbox));
    }

    /**
     * @Given the :name hidden field should contain :value
     *
     * @param string $name
     * @param string $value
     *
     * @throws \Exception
     */
    public function assertHiddenFieldContains($name, $value)
    {
        $node   = $this->getHiddenField($name);
        $value  = DataContext::replace($value);
        $actual = $node->getValue();

        $this->checkFieldContainsValue($name, $value, $actual);
    }

    /**
     * @Then the :name multiple field should contain :value
     *
     * @param string $name
     * @param string $value
     *
     * @throws \Exception
     */
    public function assertMultiFieldContains($name, $value)
    {
        $name   = DataContext::replace($name);
        $value  = DataContext::replace($value);
        $node   = $this->assertSession()->fieldExists($name);
        $actual = $node->getValue();
        if (is_array($actual)) {
            $actual = implode(',', $actual);
        }

        $this->checkFieldContainsValue($name, $value, $actual);
    }

    /**
     * @override
     */
    public function assertPageAddress($page)
    {
        $page = DataContext::replace($page);

        parent::assertPageAddress($page);
    }

    /**
     * @override
     *
     * @param string $name
     * @param string $value
     */
    public function fillField($name, $value)
    {
        $name  = DataContext::replace($name);
        $value = DataContext::replace($value);

        parent::fillField($name, $value);
    }

    /**
     * @When /^(?:|I )fill in hidden field "(?P<field>(?:[^"]|\\")*)" with "(?P<value>(?:[^"]|\\")*)"$/
     * @When /^(?:|I )fill in hidden field "(?P<field>(?:[^"]|\\")*)" with:$/
     * @When /^(?:|I )fill in hidden field "(?P<value>(?:[^"]|\\")*)" for "(?P<field>(?:[^"]|\\")*)"$/
     *
     * @param string $field
     * @param string $value
     */
    public function fillHiddenField($field, $value)
    {
        $node  = $this->getHiddenField($field);
        $value = DataContext::replace($value);

        $node->setValue($value);
    }

    /**
     * @override
     *
     * @param string $select
     * @param string $option
     */
    public function selectOption($select, $option)
    {
        $select = DataContext::replace($select);
        $option = DataContext::replace($option);

        parent::selectOption($select, $option);
    }

    /**
     * @override
     *
     * @param string $select
     * @param string $option
     */
    public function additionallySelectOption($select, $option)
    {
        $select = DataContext::replace($select);
        $option = DataContext::replace($option);

        parent::additionallySelectOption($select, $option);
    }

    /**
     * @override
     *
     * @param string $option
     */
    public function checkOption($option)
    {
        $option = DataContext::replace($option);
        parent::checkOption($option);
    }

    /**
     * @override
     *
     * @param string $option
     */
    public function uncheckOption($option)
    {
        $option = DataContext::replace($option);
        parent::uncheckOption($option);
    }

    /**
     * @override
     *
     * @param string $element
     * @param string $value
     */
    public function assertElementContains($element, $value)
    {
        $element = DataContext::replace($element);
        $value   = DataContext::replace($value);

        parent::assertElementContains($element, $value);
    }

    /**
     * @param string $name
     * @param string $value
     * @param string $actual
     *
     * @throws ExpectationException
     */
    private function checkFieldContainsValue($name, $value, $actual)
    {
        $regex = '/^'.preg_quote($value, '$/').'/ui';
        if (!preg_match($regex, $actual)) {
            $message = sprintf('The field "%s" value is "%s", but "%s" expected.', $name, $actual, $value);
            throw new ExpectationException($message, $this->getSession());
        }
    }

    /**
     * @param string $name
     *
     * @throws \Exception
     *
     * @return \Behat\Mink\Element\NodeElement
     */
    private function getHiddenField($name)
    {
        $name = DataContext::replace($name);
        $node = $this->getSession()->getPage()->find('css', 'input[type="hidden"][name="'.$name.'"]');
        if (null === $node) {
            throw new \Exception("Field $name not found");
        }

        return $node;
    }
}
