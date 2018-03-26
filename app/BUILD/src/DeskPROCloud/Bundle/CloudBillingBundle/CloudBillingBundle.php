<?php

namespace DeskPROCloud\Bundle\CloudBillingBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class CloudBillingBundle extends Bundle
{
    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    public function getPath()
    {
        return __DIR__;
    }
}
