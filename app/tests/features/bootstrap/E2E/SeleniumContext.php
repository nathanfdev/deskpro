<?php

namespace DpBehat\E2E;

use Behat\MinkExtension\Context\RawMinkContext;

/**
 * Some selenium helpers
 */
class SeleniumContext extends RawMinkContext
{
    /**
     * @Then I dump html to log
     */
    public function iDumpHtmlToLog()
    {
        error_log($this->getSession()->getCurrentUrl());
        error_log($this->getSession()->getPage()->getHtml());
    }
}