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

namespace DpIntegrationTests\DeskPRO\ApiResult;

abstract class AbstractApiResultTest extends \DpIntegrationTestCase
{
    /**
     * @var \DeskPRO\Api
     */
    private $api;

    /** @var \DeskPRO\Api */
    private $limitedAccessApi;

    public function runBefore()
    {
        $this->helper->enableDestructiveDatabaseSet('ApiSampleDb');
    }

    /**
     * @return \DeskPRO\Api
     */
    public function getApi()
    {
        if (!$this->api) {
            $url       = defined('DP_TESTING_API_URL') ? DP_TESTING_API_URL : 'http://localhost:8888';
            $this->api = new \DeskPRO\Api($url, '1:XXXXXXXXXXXXXXXXXXXXXXXXX');
        }

        return $this->api;
    }

    /**
     * @param $expected
     * @param $actual
     * @param string $message
     */
    public static function assertApiArrayEqual($expected, $actual, $message = '')
    {
        $normalize = function ($expected, $actual) use (&$normalize) {
            // Hack to 'pass' trivial differences (e.g., a date that is different becase we just inserted it)
            if (is_array($expected) && is_array($actual)) {
                foreach ($actual as $k => $v) {
                    if (isset($expected[$k]) && gettype($expected[$k]) === gettype($v)) {
                        if (is_array($v)) {
                            $expected[$k] = $normalize($expected[$k], $v);
                        } else {
                            $expected[$k] = $v;
                        }
                    }
                }
            }

            return $expected;
        };

        self::assertEquals($normalize($expected, $actual), $actual, $message);
    }

    /**
     * @return \Application\DeskPRO\DBAL\Connection
     */
    public function getDb()
    {
        return $this->helper->getSymfonyContainer()->getDb();
    }

    public function getApiWithLimitedAccess()
    {
        if (!$this->limitedAccessApi) {
            $url                    = defined('DP_TESTING_API_URL') ? DP_TESTING_API_URL : 'http://localhost:8888';
            $this->limitedAccessApi = new \DeskPRO\Api($url, '2:YYYYYYYYYYYYYYYYYYYYYYYYY');
        }

        return $this->limitedAccessApi;
    }

    protected function _getIgnoreKeys($entity)
    {
        // Some values keep updating with time, so we need to check/unset them
        $ignoreKeys = array(
            'person' => array(
                'date_created',
                'date_created_ts',
                'date_created_ts_ms',
                'date_password_set',
                'date_password_set_ts',
                'date_password_set_ts_ms',
            ),
            'person_email' => array(
                'date_created',
                'date_created_ts',
                'date_created_ts_ms',
                'date_validated',
                'date_validated_ts',
                'date_validated_ts_ms',
            ),
            'ticket' => array(
                'ref',
                'auth',
                'access_code',
                'access_code_email_body_token',
                'access_code_email_header_token',
                'date_first_agent_assign',
                'date_first_agent_assign_ts',
                'date_first_agent_assign_ts_ms',
                'date_first_agent_reply',
                'date_first_agent_reply_ts',
                'date_first_agent_reply_ts_ms',
                'date_last_agent_reply',
                'date_last_agent_reply_ts',
                'date_last_agent_reply_ts_ms',
                'date_user_waiting',
                'date_user_waiting_ts',
                'date_user_waiting_ts_ms',
                'date_status',
                'date_status_ts',
                'date_status_ts_ms',
                'date_created',
                'date_created_ts',
                'date_created_ts_ms',
                'total_user_waiting_real',
                'total_user_waiting_work',
                'current_user_waiting',
                'current_user_waiting_work',
                'total_to_first_reply',
                'total_to_first_reply_work',
                'total_to_resolution',
                'total_to_resolution_work',
                'total_user_waiting',
                'waiting_times',
            ),
            'ticket_message' => array(
                'date_created',
                'date_created_ts',
                'date_created_ts_ms',
            ),
        );

        if (array_key_exists($entity, $ignoreKeys)) {
            return $ignoreKeys[$entity];
        }
    }

    public function assertIsValidDateTime($subject, $allow_null = true)
    {
        if ($allow_null && $subject === null) {
            return true;
        }

        return $this->assertFalse(!strtotime($subject));
    }

    public function assertIsValidTimestamp($subject, $field = '')
    {
        return $this->assertGreaterThan(0, $subject, $field);
    }

    protected function _getExpectedTicket()
    {
        return require 'Tickets'.DIRECTORY_SEPARATOR.'Data'.DIRECTORY_SEPARATOR.'ExpectedTicket.php';
    }

    protected function getDateTimeFields($entity)
    {
        $datetimeFields = array(
            'ticket' => array(
                'date_created',
                'date_first_agent_reply',
                'date_last_agent_reply',
                'date_user_waiting',
                'date_status',
            ),
            'person' => array(
                'date_created',
                'date_password_set',
            ),
            'person_email' => array(
                'date_created',
            ),
            'ticket_message' => array(
                'date_created',
            ),
        );

        return isset($datetimeFields[$entity]) ? $datetimeFields[$entity] : null;
    }

    protected function getTimestampFields($entity)
    {
        $timestampFields = array(
            'ticket' => array(
                'date_created_ts',
                'date_created_ts_ms',
                'date_created_ts',
                'date_created_ts_ms',
                'date_first_agent_reply_ts',
                'date_first_agent_reply_ts_ms',
                'date_last_agent_reply_ts',
                'date_last_agent_reply_ts_ms',
                'date_user_waiting_ts',
                'date_user_waiting_ts_ms',
                'date_status_ts',
                'date_status_ts_ms',
            ),
            'person' => array(
                'date_created_ts',
                'date_created_ts_ms',
                'date_password_set_ts',
                'date_password_set_ts_ms',
            ),
            'person_email' => array(
                'date_created_ts',
                'date_created_ts_ms',
            ),
            'ticket_message' => array(
                'date_created_ts',
                'date_created_ts_ms',
            ),
        );

        return isset($timestampFields[$entity]) ? $timestampFields[$entity] : null;
    }
}
