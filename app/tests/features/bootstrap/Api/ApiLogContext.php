<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
        $this->getContainer()->get('settings_resolver')->setSetting('api_log.enabled', true);
    }

    /**
     * @Then api log should appear in table
     */
    public function checkLog()
    {
        $api_log = $this->getEntityRepo('DeskPRO\Bundle\AppBundle\Entity\ApiLog')->find(1);
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
        $api_log = $this->getEntityRepo('DeskPRO\Bundle\AppBundle\Entity\ApiLog')->findOneBy(['request_id' => $id]);
        if (!$api_log) {
            throw new \RuntimeException(
                'Api Log was not added!'
            );
        }
    }

    /**
     * Add an header element in a request.
     *
     * @Given I set duplcicate mode as :dup_mode, failure mode as :fail_mode, eager as :eager in request
     */
    public function iAddRequestHeaderEqualTo($dup_mode, $fail_mode, $eager)
    {
        $header = [
            'duplicate_mode' => $dup_mode,
            'failure_mode'   => $fail_mode,
            'eager'          => $eager,
        ];

        $this->rest_context->iAddHeaderEqualTo('X-DeskPRO-Client-Request-Options', json_encode($header));
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
            ->setStartTime(time());
        $this->persistAndFlush($log);
    }
}
