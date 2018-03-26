<?php

/**
 * DeskPRO.
 */

namespace DpBehat\Portal;

use Behat\Behat\Tester\Exception\PendingException;

class PageObjectContext extends BasePortalContext
{
    /**
     * @Given I am on the :page page
     */
    public function iAmOnThePage($page)
    {
        throw new PendingException();
    }
}
