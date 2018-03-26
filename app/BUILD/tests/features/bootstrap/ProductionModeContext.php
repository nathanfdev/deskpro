<?php

/**
 * DeskPRO.
 */

namespace DpBehat;

/**
 * Class ProductionModeContext.
 */
class ProductionModeContext extends BaseContext
{
    /**
     * @var string DP_DIR relative flag file path
     */
    const FLAG = '/var/tmp/behat_production_mode_flag.tmp';

    /**
     * @BeforeSuite
     */
    public static function enableProductionMode()
    {
        file_put_contents(
            DP_DIR.self::FLAG,
<<<'CONTENTS'
This file is used by behat as a production environment flag, your testing environment should be configured to set
prod/test mode depending on this file presence, your production environment should not take this file into account.
CONTENTS
        );
    }

    /**
     * @AfterSuite
     */
    public static function disableProductionMode()
    {
        unlink(DP_DIR.self::FLAG);
    }

    /**
     * @BeforeScenario
     */
    public function iGoToTheHomePage()
    {
        $this->visitPath('/');
    }

    /**
     * @Given I log in as :who from the portal
     */
    public function iLogInAs($who)
    {
        $users = $this->get('user_details');

        $this->visitPath('/login');
        $page = $this->getSession()->getPage();
        $page->fillField('login_username', $users->getEmail($who));
        $page->fillField('login_password', $users->getPass($who));
        $page->pressButton('login_button');
        echo 'URL after login: ', $this->getSession()->getCurrentUrl();

        // Can't access API right after login w/o visiting this page
        $this->visitPath('/new-agent/');
    }
}
