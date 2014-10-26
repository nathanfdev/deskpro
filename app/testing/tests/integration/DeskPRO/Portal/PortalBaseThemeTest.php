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
 * @subpackage
 */

namespace DpIntegrationTests\DeskPRO\Portal;


use DeskPRO\Kernel\PortalKernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Client;

class PortalBaseThemeTest extends WebTestCase
{
	public function testHome()
	{
		$client = static::createClient();

		$this->test200codeForPath($client, '/');

		$this->test200codeForPath($client, '/kb');
		$this->test200codeForPath($client, '/kb/slugggg');
		$this->test200codeForPath($client, '/kb/posts/slugggggg');

		$this->test200codeForPath($client, '/downloads');
		$this->test200codeForPath($client, '/downloads/sluggg');
		$this->test200codeForPath($client, '/downloads/files/adfdfa');
		$this->test200codeForPath($client, '/downloads/files/asdddd/download');

		$this->test200codeForPath($client, '/news');
		$this->test200codeForPath($client, '/news/slugggg');
		$this->test200codeForPath($client, '/news/posts/slugggggg');

		$this->test200codeForPath($client, '/search');

		$this->test200codeForPath($client, '/new-ticket');

		$this->test200codeForPath($client, '/tickets');
		$this->test200codeForPath($client, '/tickets/{ref}');
	}


	/**
	 * Creates a Client.
	 *
	 * @param array $options An array of options to pass to the createKernel class
	 * @param array $server  An array of server parameters
	 * @return Client A Client instance
	 */
	protected static function createClient(array $options = array(), array $server = array())
	{
		static::bootKernel($options);

		$client = new Client(static::$kernel);
		$client->setServerParameters($server);

		return $client;
	}

	public static function createKernel(array $options = array())
	{
		require_once DP_ROOT.'/sys/Kernel/PortalKernel.php';
		return new PortalKernel('test', true);
	}


	/**
	 * @param $client
	 * @param $path
	 */
	protected function test200codeForPath($client, $path)
	{
		$crawler = $client->request('GET', $path);
		$this->assertEquals(200, $client->getResponse()->getStatusCode());
	}
}
