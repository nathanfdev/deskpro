<?php

namespace DeskPRO\Bundle\UpdateBundle\BuildActivate\HelpdeskState;

use DeskPRO\Bundle\UpdateBundle\BuildActivate\BuildActivatorException;

class HelpdeskStateException extends BuildActivatorException
{
    const SET_OFFLINE_FAILED = 100;
    const SET_ONLINE_FAILED  = 200;
    const SET_BUILD_FAILED   = 300;
}
