<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace Application\DeskPRO\JIRA;

use Application\DeskPRO\Service\JIRA;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;

class Api
{
    const API_BASE_PATH = 'rest/api/2';

    protected $service;
    protected $oauth;

    public function __construct(JIRA $service)
    {
        $this->service = $service;
    }

    /**
     * @return OAuthWrapper
     */
    public function getOAuth()
    {
        if (!$this->oauth) {
            $this->oauth = new OAuthWrapper($this->service);
        }

        return $this->oauth;
    }

    /**
     * @param $endpoint
     * @param string $method
     * @param array  $headers
     * @param array  $params
     *
     * @throws ApiCoreException
     * @throws ApiErrorsException
     *
     * @return mixed
     */
    public function call($endpoint, $method = 'GET', array $headers = [], $params = [])
    {
        try {
            if ($headers) {
                $params[RequestOptions::HEADERS] = $headers;
            }
            $response = $this->getOAuth()->getClient()->request($method, $endpoint, $params);

            return @\json_decode((string) $response->getBody(), 1);
        } catch (ClientException $e) {
            $response = (string) $e->getResponse()->getBody();
            $json     = @\json_decode($response, 1);

            if (!empty($json['errors'])) {
                throw new ApiErrorsException($json['errors'], $e);
            }

            if (!empty($json['errorMessages'])) {
                throw new ApiCoreException($json['errorMessages'], $e);
            }

            throw $e;
        }
    }

    /**
     * @param $endpoint
     * @param array $params
     *
     * @return mixed
     */
    public function get($endpoint, array $params = [])
    {
        return $this->call(self::API_BASE_PATH.$endpoint, 'GET', [], [RequestOptions::QUERY => $params]);
    }

    /**
     * @param $endpoint
     * @param array $params
     *
     * @return mixed
     */
    public function post($endpoint, array $params = [])
    {
        return $this->call(
            self::API_BASE_PATH.$endpoint,
            'POST',
            [],
            [RequestOptions::JSON => $params]
        );
    }

    /**
     * @param $endpoint
     * @param array $params
     *
     * @return mixed
     */
    public function put($endpoint, array $params = [])
    {
        return $this->call(
            self::API_BASE_PATH.$endpoint,
            'PUT',
            [],
            [RequestOptions::JSON => $params]
        );
    }

    /**
     * @param $endpoint
     * @param array $params
     *
     * @return mixed
     */
    public function delete($endpoint, array $params = [])
    {
        return $this->call(self::API_BASE_PATH.$endpoint, 'DELETE', [], $params);
    }

    /**
     * @param array $data
     *
     * @return mixed
     */
    public function createIssue(array $data)
    {
        return $this->post('/issue', $data);
    }

    /**
     * @param $json
     *
     * @return mixed
     */
    public function createIssueJson($json)
    {
        return $this->call(
            self::API_BASE_PATH.'/issue',
            'POST',
            ['content-type'       => 'application/json'],
            [RequestOptions::BODY => $json]
        );
    }

    /**
     * @param $id
     * @param array $data
     *
     * @return mixed
     */
    public function updateIssue($id, array $data)
    {
        return $this->put(sprintf('/issue/%d', $id), $data);
    }

    /**
     * @param $id
     * @param $json
     *
     * @return mixed
     */
    public function updateIssueJson($id, $json)
    {
        return $this->call(
            sprintf('%s/issue/%d', self::API_BASE_PATH, $id),
            'PUT',
            ['content-type'       => 'application/json'],
            [RequestOptions::BODY => $json]
        );
    }

    /**
     * @param $jql
     * @param array $fields
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function searchIssues($jql, array $fields)
    {
        return $this->post('/search', [
            'jql'    => $jql,
            'fields' => $fields,
            'expand' => ['renderedFields'],
        ]);
    }
}
