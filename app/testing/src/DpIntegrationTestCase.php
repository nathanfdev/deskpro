<?php

class DpIntegrationTestCase extends \Codeception\TestCase\Test
{
    /**
     * @var array
     */
    protected $backupGlobals = array(
        'DP_CONFIG'
    );

    /**
     * @var \IntegrationGuy
     */
    protected $helper;

    protected function _before()
    {
        $this->helper = $this->codeGuy;
        $this->runBefore();
    }

    protected function _after()
    {
        $this->runAfter();
    }

    public function runBefore() {}
    public function runAfter() {}
}
