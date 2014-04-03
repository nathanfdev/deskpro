<?php
namespace DpUnitTests\DeskPRO\ApiResult;

abstract class AbstractApiResultTest extends \DpIntegrationTestCase
{
	/**
	 * @var \DeskPRO\Api
	 */
	private $api;


	public function runBefore()
	{
		$this->helper->enableDatabaseSet('ApiSampleDb');
	}


	/**
	 * @return \DeskPRO\Api
	 */
	public function getApi()
	{
		if (!$this->api) {
			$this->api = new \DeskPRO\Api('http://localhost:8888', '1:XXXXXXXXXXXXXXXXXXXXXXXXX');
		}

		return $this->api;
	}
}