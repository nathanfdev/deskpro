<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Command;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\ApiKey;
use DeskPRO\Bundle\AppBundle\Entity\ApiKeyAction;
use DeskPRO\Bundle\AppBundle\Util\HttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
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
        $this->addOption('v1', null, InputOption::VALUE_NONE, 'Use v1 api');
        $this->addOption('url', null, InputOption::VALUE_REQUIRED, 'Use this API url instead of generating the URL automatically based on the current helpdesk.');
        $this->addOption('api-key', null, InputOption::VALUE_REQUIRED, 'Use this API key. When this option is not used, the command will create a key for the first admin in the database. Use the special string "NONE" to not send any key (e.g., to test open/public APIs).');
        $this->addOption('raw', null, InputOption::VALUE_NONE, 'Output the API result directly without any other info or JSON decoding');
        $this->addOption('printr', null, InputOption::VALUE_NONE, 'Output as PHP array');
        $this->addOption('as-form', null, InputOption::VALUE_NONE, 'For PUT/POST requests, send the data payload as a form instead of JSON which is the default');
        $this->addOption('curl', null, InputOption::VALUE_NONE, 'Output request as a cURL command that can be copy+pasted');
        $this->addArgument('path', InputArgument::REQUIRED, 'The API endpoint to request');
        $this->addArgument('data', InputArgument::OPTIONAL, 'Data to send. This should be a JSON-encoded string. Specify a PHP file that returns an array by prefixing the string with @. E.g., @/my-data.php');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $reqType = 'GET';
        $isV2    = !$input->getOption('v1');
        $apiPath = $isV2 ? '/v2/' : '/';
        $asForm  = $input->getOption('as-form');
        $curl    = ['curl'];

        //------------------------------
        // Get the data to post
        //------------------------------

        $dataArg = $input->getArgument('data');
        $data    = null;
        if ($dataArg) {
            $reqType = 'POST';
            if ($dataArg[0] === '@') {
                $dataArg = substr($dataArg, 1);
                if (!file_exists($dataArg)) {
                    $output->writeln("<error>No such file exists: $dataArg</error>");

                    return 1;
                }

                $data = require $dataArg;
                if (!is_array($data)) {
                    $output->writeln("<error>Data file did not return array: $dataArg</error>");

                    return 1;
                }
            } else {
                $data = json_decode($dataArg, true);

                if (!is_array($data)) {
                    $output->writeln('<error>Data did not decode into an array. Make sure you specified a JSON string.</error>');

                    return 1;
                }
            }
        }

        if ($input->getOption('get')) {
            $reqType = 'GET';
        } elseif ($input->getOption('post')) {
            $reqType = 'POST';
        } elseif ($input->getOption('put')) {
            $reqType = 'PUT';
        } elseif ($input->getOption('delete')) {
            $reqType = 'DELETE';
        }

        //------------------------------
        // Get the path and api token
        //------------------------------

        if ($input->getOption('url')) {
            $baseUrl = trim($input->getOption('url'), '/').'/';
            if (strpos($baseUrl, '/api/') === false) {
                if (strpos($baseUrl, '/index.php/') === false) {
                    $baseUrl .= 'index.php/';
                }
                $baseUrl .= "api$apiPath";
            }
            if (!preg_match('#^https?://#', $baseUrl)) {
                $baseUrl = 'http://'.$baseUrl;
            }
        } else {
            $baseUrl = trim(App::getContainer()->getBrandSetting('core.deskpro_url'), '/')."/api$apiPath";
        }
        $path = trim($input->getArgument('path'), '/');

        $apiKey = $input->getOption('api-key');
        if (!$apiKey) {
            $key = App::getOrm()->getRepository(ApiKey::class)->findOneBy(['note' => '[dpdev:test-api]']);

            if (!$key) {
                $firstAdmin = App::getOrm()->createQuery(
                    '
                    SELECT p
                    FROM DeskPRO:Person p
                    WHERE p.is_agent = TRUE AND p.can_admin = TRUE
                    ORDER BY p.id ASC
                '
                )->setMaxResults(1)->getOneOrNullResult();

                if ($firstAdmin) {
                    $key         = new ApiKey();
                    $key->person = $firstAdmin;
                    $key->note   = '[dpdev:test-api]';
                    $keyAction   = new ApiKeyAction();
                    $keyAction->setKey($key)->setAction('*');
                    App::getOrm()->persist($key);
                    App::getOrm()->persist($keyAction);
                    App::getOrm()->flush($key);
                }
            }

            if (!$key) {
                $output->writeln('<error>Could not find a key to use</error>');

                return 1;
            }

            $apiKey = $key->getKeyString();
        }

        if ($isV2) {
            $apiKey = 'key '.$apiKey;
        }

        //------------------------------
        // Make the request
        //------------------------------

        $headers = [];

        if ($isV2) {
            $headers['Authorization'] = $apiKey;
            $curl[]                   = '-H \'Authorization: '.$apiKey.'\'';
        } else {
            $headers['X-DeskPRO-API-Key'] = $apiKey;
            $curl[]                       = '-H \'X-DeskPRO-API-Key: '.$apiKey.'\'';
        }

        if ($apiKey !== 'NONE') {
            $headers['X-DeskPRO-API-Key'] = $apiKey;
        }

        $httpClient = new HttpClient([
            'base_uri'              => $baseUrl,
            RequestOptions::VERIFY  => false,
            RequestOptions::HEADERS => $headers,
        ]);

        try {
            switch ($reqType) {
                case 'GET':
                    if ($data) {
                        $dataUrl = http_build_query($data);
                        if (strpos($path, '?')) {
                            $path .= "&$dataUrl";
                        } else {
                            $path .= "?$dataUrl";
                        }
                    }
                    $response = $httpClient->get($path);
                    $curl[]   = '-XGET';
                    $curl[]   = escapeshellarg($baseUrl.$path);
                    break;

                case 'POST':
                    $curl[] = '-XPOST';
                    if ($asForm) {
                        $response = $httpClient->post($path, [RequestOptions::FORM_PARAMS => $data]);
                    } else {
                        $response = $httpClient->post($path, [RequestOptions::JSON => $data]);
                    }
                    break;

                case 'PUT':
                    $curl[] = '-XPUT';
                    if ($asForm) {
                        $response = $httpClient->put($path, [RequestOptions::FORM_PARAMS => $data]);
                    } else {
                        $response = $httpClient->put($path, [RequestOptions::JSON => $data]);
                    }
                    break;

                case 'DELETE':
                    $curl[] = '-XDELETE';
                    if ($data) {
                        $dataUrl = http_build_query($data);
                        if (strpos($path, '?')) {
                            $path .= "&$dataUrl";
                        } else {
                            $path .= "?$dataUrl";
                        }
                    }
                    $response = $httpClient->delete($path);
                    break;

                default:
                    return 1;
            }
        } catch (RequestException $e) {
            $response = $e->getResponse();
        } catch (ClientException $e) {
            $response = $e->getResponse();
        }

        if ($input->getOption('curl')) {
            if ($reqType == 'POST' || $reqType === 'PUT') {
                if ($asForm) {
                    $tmpName = sys_get_temp_dir().DIRECTORY_SEPARATOR.date('YmdHis').'-'.uniqid('');
                    file_put_contents($tmpName, http_build_query($data));
                    $curl[] = '-d \'@'.$tmpName.'\'';
                    $curl[] = '-H \'Content-Type: application/x-www-form-urlencoded\'';
                } else {
                    $tmpName = sys_get_temp_dir().DIRECTORY_SEPARATOR.date('YmdHis').'-'.uniqid('');
                    file_put_contents($tmpName, json_encode($data));
                    $curl[] = '-d \'@'.$tmpName.'\'';
                    $curl[] = '-H \'Content-Type: application/json\'';
                }
            }

            $output->writeln('<info>'.implode(' ', $curl).'</info>');
            $output->writeln('');

            return 0;
        }

        if ($input->getOption('raw')) {
            echo $response->getBody();
        } else {
            if ($response->getStatusCode() >= 500 && $response->getStatusCode() <= 599) {
                $output->write("<error>Error</error>\n");
            } else {
                $output->write("<info>Success</info>\n");
            }
            $output->writeln('<info>Request URI:    '.$baseUrl.$path.'</info>');
            $output->writeln('<info>Request Method: '.$reqType.'</info>');
            $output->writeln('<info>Status Code:    '.$response->getStatusCode().'</info>');
            $output->writeln('<info>Content Type:   '.$response->getHeaderLine('Content-Type').'</info>');

            $res  = (string) $response->getBody();
            $json = @json_decode($res, true);

            if ($json) {
                if ($input->getOption('printr')) {
                    print_r($json);
                } else {
                    echo json_encode($json, JSON_PRETTY_PRINT);
                }
            } else {
                echo $res;
            }

            echo "\n";
        }

        return 0;
    }
}
