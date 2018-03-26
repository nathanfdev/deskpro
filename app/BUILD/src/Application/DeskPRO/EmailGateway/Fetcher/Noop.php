<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Fetcher;

class Noop extends AbstractFetcher
{
    protected function _initConnection()
    {
        // noop
    }

    protected function _readNext()
    {
        return;
    }

    protected function _doneRead($id)
    {
        // noop
    }
}
