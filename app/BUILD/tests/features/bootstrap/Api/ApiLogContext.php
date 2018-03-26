<?php

namespace DpBehat\Api;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use DeskPRO\Bundle\AppBundle\Entity\ApiLog;
use DpBehat\BaseContext;

/**
 * Defines application features from the specific context.
 */
class ApiLogContext extends BaseContext
{
    protected $request_id;

    /**
     * @var RestContext
     */
    private $rest_context;

    /**
     * @Given I have enabled api log feature
     */
    public function enableApiLog()
    {
        $this->get('settings_resolver')->setSetting('api_log.enabled', true);
    }

    /**
     * @Then api log should appear in table
     */
    public function checkLog()
    {
        $api_log = $this->repository('DeskPRO\Bundle\AppBundle\Entity\ApiLog')->find(1);
        if (!$api_log) {
            throw new \RuntimeException(
                'Api Log was not added!'
            );
        }
    }

    /**
     * @Then api log with :id id should appear in table
     *
     * @param $id
     */
    public function checkLogById($id)
    {
        $api_log = $this->repository('DeskPRO\Bundle\AppBundle\Entity\ApiLog')->findOneBy(['request_id' => $id]);
        if (!$api_log) {
            throw new \RuntimeException(
                'Api Log was not added!'
            );
        }
    }

    /**
     * Add an header element in a request.
     *
     * @Given I set duplicate mode as :dup_mode, failure mode as :fail_mode, eager as :eager in request
     */
    public function iAddRequestHeaderEqualTo($dup_mode, $fail_mode, $eager)
    {
        $header = [
            'duplicate_mode' => $dup_mode,
            'failure_mode'   => $fail_mode,
            'eager'          => $eager,
        ];

        $this->rest_context->iAddHeaderEqualTo('X-DeskPRO-Client-Request-Options', json_encode($header), true);
    }

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();

        $this->rest_context = $environment->getContext('DpBehat\Api\RestContext');
    }

    /**
     * @Given There is the eager log with :id to :url
     *
     * @param string $id
     * @param string $url
     */
    public function addEagerLogWith($id, $url)
    {
        $log = new ApiLog();
        $log
            ->setRequestedUri($url)
            ->setRequestId($id)
            ->setRequestData([])
            ->setStartTime(time())
            ->setMode('test')
            ->setMethod('get')
            ->setCredentials('test');
        $this->persistAndFlush($log);
    }
}
