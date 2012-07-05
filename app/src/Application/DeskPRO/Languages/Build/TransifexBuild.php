<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Application\DeskPRO\Languages\Build;

use Zend\Http\Client as HttpClient;
use Zend\Http\Request as HttpRequest;

class TransifexBuild extends AbstractBuild
{
	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @var string
	 */
	protected $username;

	/**
	 * @var string
	 */
	protected $password;

	/**
	 * @var \Zend\Http\Client
	 */
	protected $http;

	/**
	 * @param string $url
	 * @param string $username
	 * @param string $password
	 */
	public function __construct($url, $username, $password)
	{
		$this->url      = rtrim($url, '/') . '/api/2';
		$this->username = $username;
		$this->password = $password;
	}


	/**
	 * @return \Zend\Http\Client
	 */
	public function getHttpClient()
	{
		if (!$this->http) {
			$http = new \Zend\Http\Client(null, array('strictredirects' => true));
			$http->setAuth($this->username, $this->password);
		}

		return $http;
	}


	/**
	 * @param string $path
	 * @return array
	 * @throws \RuntimeException
	 */
	public function restGet($path)
	{
		$http = $this->getHttpClient();
		$http->setUri($this->url . '/' . ltrim($path, '/'));

		$req = new HttpRequest();
		$req->setMethod(HttpRequest::METHOD_GET);
		$req->setUri($http->getUri());

		$res = $http->send($req);

		if (!$res->isSuccess()) {
			throw new \RuntimeException("Server error : {$res->getStatusCode()}", $res->getStatusCode());
		}

		$body = $res->getBody();
		$data = json_decode($body, true);

		return $data;
	}


	/**
	 * @param string $id
	 * @return array
	 */
	public function getCategoryWords($id, $section, $category)
	{
		$locale = $this->getLangPackInfo()->getLangInfo($id, 'locale');

		$project = $this->getProjectName($section);

		try {
			$data = $this->restGet("project/$project/resource/$category/translation/$locale");
		} catch (\RuntimeException $e) {
			if ($e->getCode() == 404) {
				$this->getLogger()->logInfo("$id is missing $section.$category");
				return array();
			}

			throw $e;
		}

		$po = $data['content'];

		$stream = fopen('php://memory', 'w+');
		fwrite($stream, $po);
		rewind($stream);

		$store  = new \TempPoMsgStore();
		$parser = new \POParser($store);
		$parser->parseEntriesFromStream($stream);

		fclose($stream);

		$words = array();
		foreach ($store->read() as $line) {
			if (empty($line['msgid']) || empty($line['msgstr'])) {
				continue;
			}

			$words[trim($line['msgid'])] = trim($line['msgstr']);
		}

		return $words;
	}


	/**
	 * Build a language
	 *
	 * @param string $id The standard DeskPRO ID for the language
	 */
	public function buildLanguage($id)
	{
		$this->clearLang($id);

		foreach ($this->getDefaultSections() as $section) {
			foreach ($this->getDefaultCategories($section) as $category) {
				$words = $this->getCategoryWords($id, $section, $category);
				if ($words) {
					$this->writeLangFile($id, $section, $category, $words);
				}
			}
		}
	}


	/**
	 * Gets the project name used in transifex
	 *
	 * @param string $section
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function getProjectName($section)
	{
		switch ($section) {
			case 'user': return 'dpuser';
			case 'agent': return 'dpagent';
			case 'admin': return 'dpadmin';
		}

		throw new \InvalidArgumentException("Invalid section $section");
	}
}