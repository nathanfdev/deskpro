<?php

namespace DpBehat;

/**
 * Class PeopleContext.
 */
class EmailGatewayContext extends BaseContext
{
    /**
     * @Given I skip Email gateway EmailSource runner shutdown logic
     *
     * Check DeskPRO/EmailGateway/Runner::ensureSourceStatus
     * Without this hack with gobal var Runner try to execute query in shutdown functino when Behat already destroyed connection
     */
    public function skipRunnerShoutdownLogic()
    {
        // check DeskPRO/EmailGateway/Runner::ensureSourceStatus
        global $DP_SET_SOURCE_STATUS;
        $DP_SET_SOURCE_STATUS = [];
    }
}
