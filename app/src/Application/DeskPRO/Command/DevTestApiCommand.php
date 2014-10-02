<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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

namespace Application\DeskPRO\Command;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\ApiKey;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DevTestApiCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('dpdev:test-api');
		$this->addOption('get', null, InputOption::VALUE_NONE, 'Send a GET request (default when no data is sent)');
		$this->addOption('post', null, InputOption::VALUE_NONE, 'Send a POST request (default when data is sent)');
		$this->addOption('put', null, InputOption::VALUE_NONE, 'Send a PUT request');
		$this->addOption('delete', null, InputOption::VALUE_NONE, 'Send a DELETE request');
		$this->addOption('api-key', null, InputOption::VALUE_REQUIRED, 'Use this API key. When this option is not used, the command will create a key for the first admin in the database.');
		$this->addOption('raw', null, InputOption::VALUE_NONE, 'Output the API result directly without any other info or JSON decoding');
		$this->addOption('printr', null, InputOption::VALUE_NONE, 'Output as PHP array');
		$this->addArgument('path', InputArgument::REQUIRED, 'The API endpoint to request');
		$this->addArgument('data', InputArgument::OPTIONAL, 'Data to send. This should be a JSON-encoded string. Specify a PHP file that returns an array by prefixing the string with @. E.g., @/my-data.php');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$req_type = 'GET';

		#------------------------------
		# Get the data to post
		#------------------------------

		$data_arg = $input->getArgument('data');
		$data = null;
		if ($data_arg) {
			$req_type = 'POST';
			if ($data_arg[0] === '@') {
				$data_arg = substr($data_arg, 1);
				if (!file_exists($data_arg)) {
					$output->writeln("<error>No such file exists: $data_arg</error>");
					return 1;
				}

				$data = require($data_arg);
				if (!is_array($data)) {
					$output->writeln("<error>Data file did not return array: $data_arg</error>");
					return 1;
				}
			} else {
				$data = json_decode($data_arg, true);

				if (!is_array($data)) {
					$output->writeln("<error>Data did not decode into an array. Make sure you specified a JSON string.</error>");
					return 1;
				}
			}
		}

		if ($input->getOption('get')) {
			$req_type = 'GET';
		} elseif ($input->getOption('post')) {
			$req_type = 'POST';
		} elseif ($input->getOption('put')) {
			$req_type = 'PUT';
		} elseif ($input->getOption('delete')) {
			$req_type = 'DELETE';
		}

		#------------------------------
		# Get the path and api token
		#------------------------------

		$base_url = trim(App::getSetting('core.deskpro_url'), '/') . '/index.php/api/';
		$path = trim($input->getArgument('path'), '/');

		$api_key = $input->getOption('api-key');
		if (!$api_key) {
			$key = App::getOrm()->getRepository('DeskPRO:ApiKey')->findOneBy(array('note' => '[dpdev:test-api]'));

			if (!$key) {
				$first_admin = App::getOrm()->createQuery("
					SELECT p
					FROM DeskPRO:Person p
					WHERE p.is_agent = true and p.can_admin = true
					ORDER BY p.id ASC
				")->setMaxResults(1)->getOneOrNullResult();

				if ($first_admin) {
					$key = new ApiKey();
					$key->person = $first_admin;
					$key->note=  '[dpdev:test-api]';
					App::getOrm()->persist($key);
					App::getOrm()->flush();
				}
			}

			if (!$key) {
				$output->writeln("<error>Could not find a key to use</error>");
				return 1;
			}

			$api_key = $key->getKeyString();
		}

		#------------------------------
		# Make the request
		#------------------------------

		$http_client = new \Guzzle\Http\Client($base_url, array(
			'ssl.certificate_authority' => false
		));
		$http_client->setDefaultHeaders(array(
			'X-DeskPRO-API-Key' => $api_key
		));

		switch ($req_type) {
			case 'GET':
				if ($data) {
					$data_url = http_build_query($data);
					if (strpos($path, '?')) {
						$path .= "&$data_url";
					} else {
						$path .= "?$data_url";
					}
				}
				$request = $http_client->get($path);
				break;

			case 'POST':
				$request = $http_client->post($path, null, $data);
				break;

			case 'PUT':
				$request = $http_client->post($path, null, $data);
				break;

			case 'DELETE':
				if ($data) {
					$data_url = http_build_query($data);
					if (strpos($path, '?')) {
						$path .= "&$data_url";
					} else {
						$path .= "?$data_url";
					}
				}
				$request = $http_client->delete($path);
				break;

			default:
				return 1;
		}

		try {
			$response = $request->send();
		} catch (\Guzzle\Http\Exception\RequestException $e) {
			if (method_exists($e, 'getResponse')) {
				$response = $e->getResponse();
			} else {
				throw $e;
			}
		}

		if ($input->getOption('raw')) {
			echo $response->getBody(true);
		} else {
			if (!$response->isSuccessful()) {
				$output->write("<error>Error</error>\n");
			} else {
				$output->write("<info>Success</info>\n");
			}
			$output->writeln("<info>Request URI:    " . $request->getUrl() . "</info>");
			$output->writeln("<info>Request Method: " . $request->getMethod() . "</info>");
			$output->writeln("<info>Status Code:    " . $response->getStatusCode() . "</info>");
			$output->writeln("<info>Content Type:   " . $response->getContentType() . "</info>");

			$res = $response->getBody(true);
			$json = @json_decode($res, true);

			if ($json) {
				if ($input->getOption('printr')) {
					print_r($json);
				} else {
					if (defined('JSON_PRETTY_PRINT')) {
						echo json_encode($json, JSON_PRETTY_PRINT);
					} else {
						echo $this->_jsonpp(json_encode($json));
					}
				}
			} else {
				echo $res;
			}

			echo "\n";
		}

		return 0;
	}


	private function _jsonpp($json, $istr='  ')
	{
		$result = '';
		for($p=$q=$i=0; isset($json[$p]); $p++)
		{
			$json[$p] == '"' && ($p>0?$json[$p-1]:'') != '\\' && $q=!$q;
			if(strchr('}]', $json[$p]) && !$q && $i--)
			{
				strchr('{[', $json[$p-1]) || $result .= "\n".str_repeat($istr, $i);
			}
			$result .= $json[$p];
			if(strchr(',{[', $json[$p]) && !$q)
			{
				$i += strchr('{[', $json[$p])===FALSE?0:1;
				strchr('}]', $json[$p+1]) || $result .= "\n".str_repeat($istr, $i);
			}
		}
		return $result;
	}
}