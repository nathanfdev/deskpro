<?php

namespace DpBehat\Portal;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\Element;
use Behat\Mink\Element\NodeElement;
use DpBehat\Data\DataContext;

/**
 * Class FormContext.
 */
class FormContext extends BasePortalContext
{
    /**
     * @Given I get captcha code from the form field :name
     *
     * @param string $name
     */
    public function iSetCaptchaCode($name)
    {
        $key     = sprintf('gcb_%s', $name);
        $session = $this->container()->get('session');
        $options = $session->get($key);

        DataContext::setPlaceholder('captchaCode', $options['phrase']);
    }

    /**
     * @Then I should see a form error with :message
     *
     * @param string $message
     */
    public function iShouldSeeAFormErrorWith($message)
    {
        $this->assertSession()->elementExists('css', '.error-large');
        $this->assertSession()->elementTextContains('css', '.error-large', $message);
    }

    /**
     * @Then I should see a form error list with the phrase :phrase
     *
     * @param string $phrase
     */
    public function iShouldSeeAFormErrorListWith($phrase)
    {
        $this->assertSession()->elementExists('css', '.form-error-list');
        $this->assertSession()->elementTextContains('css', '.form-error-list li span', $this->phrase($phrase));
    }

    /**
     * @Then /^I should see a form error with the phrase "(?P<phrase>(?:[^"]|\\")*)"$/
     *
     * @param string $phrase
     *
     * @throws \Exception
     */
    public function iShouldSeeAFormErrorWithPhrase($phrase)
    {
        $this->findErrorPhrase($this->getSession()->getPage(), $phrase);
    }

    /**
     * @Then /^I should not see a form error with the phrase "(?P<phrase>(?:[^"]|\\")*)"$/
     *
     * @param string $phrase
     *
     * @throws \Exception
     */
    public function iShouldNotSeeAFormErrorWithPhrase($phrase)
    {
        $phrase = $this->phrase($phrase) ?: $phrase;
        $phrase = $this->fixStepArgument($phrase);

        $container = $this->getSession()->getPage();
        $regex     = '/'.preg_quote($phrase, '/').'/ui';

        /** @var \Behat\Mink\Element\NodeElement[] $nodes */
        $nodes = $container->findAll('css', '.error-large');

        foreach ($nodes as $n) {
            if (preg_match($regex, $n->getText())) {
                throw new \Exception("The phrase \"$phrase\" was found in the text of any .error-large elements.");
            }
        }
    }

    /**
     * @Then print :locator form field errors
     *
     * @param $locator
     */
    public function printFormFieldErrors($locator)
    {
        $field = $this->assertField($locator);
        $nodes = $this->findNodeErrors($this->findFieldParentNode($field));

        foreach ($nodes as $node) {
            echo $node->getHtml();
        }
    }

    /**
     * @Then :locator form field should have :count error(s)
     *
     * @param string $locator
     * @param int    $count
     *
     * @throws \Exception
     */
    public function assertFormErrorCountForTheField($locator, $count)
    {
        $field  = $this->assertField($locator);
        $errors = $this->findNodeErrors($this->findFieldParentNode($field));

        $actual = count($errors);

        if ($actual !== $count) {
            throw new \Exception("Expected $count errors, got $actual");
        }
    }

    /**
     * @Then :locator form field should have error with the phrase :phrase
     *
     * @param string $locator
     * @param string $phrase
     *
     * @throws \Exception
     */
    public function assertFormErrorForTheField($locator, $phrase)
    {
        $field = $this->assertField($locator);
        $this->findErrorPhrase($this->findFieldParentNode($field), $phrase);
    }

    /**
     * @Then I should see the :locator field
     *
     * @param string $locator
     *
     * @throws \Exception
     *
     * @return NodeElement
     */
    public function assertField($locator)
    {
        $locator = DataContext::replace($locator);
        $field   = $this->getSession()->getPage()->findField($locator);

        if (null === $field) {
            throw new \Exception("Field $locator not found");
        }

        return $field;
    }

    /**
     * @Then I should not see the :locator field
     *
     * @param string $locator
     *
     * @throws \Exception
     */
    public function theFormShouldNotHaveField($locator)
    {
        $locator = DataContext::replace($locator);
        $field   = $this->getSession()->getPage()->findField($locator);

        if ($field) {
            throw new \Exception("Field $locator was found");
        }
    }

    /**
     * @Then the :locator form should have :expectedCount elements
     *
     * @param string $locator
     * @param int    $expectedCount
     *
     * @throws \Exception
     */
    public function theFormShouldHaveElementsCount($locator, $expectedCount)
    {
        $form = $this->getSession()->getPage()->find('css', $locator);
        if (!$form) {
            throw new \Exception("Form $locator not found");
        }

        $nodes = $form->findAll('css', 'input, textarea, select');
        $count = count($nodes);
        if ($count !== $expectedCount) {
            throw new \Exception("Form should have $expectedCount element but it has {$count}");
        }
    }

    /**
     * @Then I should see :locator form fields in following order:
     *
     * @param string    $locator
     * @param TableNode $expectedElements
     *
     * @throws \Exception
     */
    public function iShouldSeeFormElementsOrder($locator, TableNode $expectedElements)
    {
        $form = $this->getSession()->getPage()->find('css', $locator);
        if (!$form) {
            throw new \Exception("Form $locator not found");
        }

        /** @var NodeElement[] $nodes */
        $nodes = $form->findAll('css', 'input, textarea, select');
        foreach ($expectedElements as $num => $data) {
            if (!isset($nodes[$num])) {
                throw new \Exception("Form element $num not found");
            }

            $node = $nodes[$num];
            foreach (['name', 'class'] as $attribute) {
                if (!empty($data[$attribute])) {
                    $expectedValue = DataContext::replace($data[$attribute]) ?: '';
                    $elementValue  = $node->getAttribute($attribute) ?: '';

                    if ($expectedValue !== $elementValue) {
                        throw new \Exception("Form element $num expected to contain $expectedValue got $elementValue");
                    }
                }
            }
        }
    }

    /**
     * @param Element $element
     *
     * @return \Behat\Mink\Element\NodeElement[]
     */
    private function findNodeErrors(Element $element)
    {
        return $element->findAll('css', '.error-large');
    }

    /**
     * @param NodeElement $element
     *
     * @return NodeElement|bool
     */
    private function findFieldParentNode(NodeElement $element)
    {
        while (true) {
            $parent = $element->getParent();
            foreach (['column-full', 'column-half'] as $parentClass) {
                if ($parent->hasClass($parentClass)) {
                    return $parent;
                }
            }

            $element = $parent;
        }

        return false;
    }

    /**
     * @param Element $element
     * @param string  $phrase
     *
     * @throws \Exception
     */
    private function findErrorPhrase(Element $element, $phrase)
    {
        $nodes = $this->findNodeErrors($element);
        $regex = $this->getPhraseRegex($phrase);

        foreach ($nodes as $n) {
            if (preg_match($regex, $n->getText())) {
                return;
            }
        }

        $message = sprintf(
            'The phrase "%s" was not found in the text of any .error-large elements.',
            $phrase
        );

        throw new \Exception($message);
    }

    /**
     * @param string $phrase
     *
     * @return string
     */
    private function getPhraseRegex($phrase)
    {
        $phrase = DataContext::replace($phrase);
        $phrase = $this->phrase($phrase) ?: $phrase;
        $phrase = $this->fixStepArgument($phrase);

        return '/'.preg_quote($phrase, '/').'/ui';
    }

    /**
     * Returns fixed step argument (with \\" replaced back to ").
     *
     * @param string $argument
     *
     * @return string
     */
    private function fixStepArgument($argument)
    {
        return str_replace('\\"', '"', $argument);
    }
}
