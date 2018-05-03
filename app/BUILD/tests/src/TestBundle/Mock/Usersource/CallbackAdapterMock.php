<?php

namespace DpTestSrc\TestBundle\Mock\Usersource;

use Application\DeskPRO\Usersource\Adapter\AbstractAdapter;
use Application\DeskPRO\Usersource\UsersourceInfo;
use DpTestSrc\TestBundle\Mock\Usersource\Auth\CallbackAdapterStub;

/**
 * Class CallbackAdapterMock.
 */
class CallbackAdapterMock extends AbstractAdapter
{
    /**
     * {@inheritdoc}
     */
    public function getCapabilities()
    {
        return [
            UsersourceInfo::CAPABILITY_LOGIN_PULL_BTN,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function _createAuthAdapterObject()
    {
        return new CallbackAdapterStub();
    }
}
