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

namespace DpBehat\Portal;

/**
 * Class FormContext.
 */
class FormContext extends BasePortalContext
{
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
        $phrase = $this->phrase($phrase) ?: $phrase;
        $phrase = $this->fixStepArgument($phrase);

        $container = $this->getSession()->getPage();
        $regex     = '/'.preg_quote($phrase, '/').'/ui';

        /** @var \Behat\Mink\Element\NodeElement[] $nodes */
        $nodes = $container->findAll('css', '.error-large');

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
     * Returns fixed step argument (with \\" replaced back to ").
     *
     * @param string $argument
     *
     * @return string
     */
    protected function fixStepArgument($argument)
    {
        return str_replace('\\"', '"', $argument);
    }
}
