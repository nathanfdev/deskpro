<?php

namespace DpTestSrc\TestBundle\Mock\Usersource\Auth;

use Orb\Auth\Adapter\AbstractCallbackAdatper;
use Orb\Auth\Identity;
use Orb\Auth\Result;
use Orb\Auth\StateHandler\StateHandlerInterface;

/**
 * Class CallbackAdapterStub.
 */
class CallbackAdapterStub extends AbstractCallbackAdatper
{
    /**
     * {@inheritdoc}
     */
    protected function authenticateCallback(array $callback_data, StateHandlerInterface $state)
    {
        return new Result(Result::SUCCESS, new Identity(1));
    }

    /**
     * {@inheritdoc}
     */
    protected function authenticateInitialize(StateHandlerInterface $state)
    {
        return new Result(Result::SUCCESS);
    }
}
