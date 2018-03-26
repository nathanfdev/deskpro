<?php

namespace DpSys\LowScript;

class TestPing extends LowScriptAbstract
{
    public function runAction()
    {
        header('HTTP/1.0 200 OK');
        echo 'OK';
    }
}
