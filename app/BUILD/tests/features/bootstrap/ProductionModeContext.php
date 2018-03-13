<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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
