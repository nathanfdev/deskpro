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
     * Runner try to execute query in shutdown function when Behat already destroyed connection
     */
    public function skipRunnerShoutdownLogic()
    {
        global $DP_SET_SOURCE_STATUS;
        $DP_SET_SOURCE_STATUS = [];
    }
}
