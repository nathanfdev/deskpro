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

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Command;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\ApiKey;
use DeskPRO\Bundle\AppBundle\Entity\ApiKeyAction;
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

        #------------------------------
        # Get the data to post
        #------------------------------

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

        #------------------------------
        # Get the path and api token
        #------------------------------

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
            $baseUrl = trim(App::getSetting('core.deskpro_url'), '/')."/api$apiPath";
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

            if ($isV2) {
                $apiKey = 'key '.$apiKey;
            }
        }

        #------------------------------
        # Make the request
        #------------------------------

        $headers = array();

        if ($isV2) {
            $headers['Authorization'] = $apiKey;
            $curl[]                   = '-H \'Authorization: '.$apiKey.'\'';
        } else {
            $headers['X-DeskPRO-API-Key'] = $apiKey;
            $curl[]                       = '-H \'X-DeskPRO-API-Key: '.$apiKey.'\'';
        }

        $httpClient = new \Guzzle\Http\Client($baseUrl, array(
            'ssl.certificate_authority' => false,
            'request.options'           => array('headers' => $headers),
        ));
        if ($apiKey !== 'NONE') {
            $httpClient->setDefaultHeaders(array(
                'X-DeskPRO-API-Key' => $apiKey,
            ));
        }

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
                $request = $httpClient->get($path);
                $curl[]  = '-XGET';
                $curl[]  = escapeshellarg($baseUrl.$path);
                break;

            case 'POST':
                $curl[] = '-XPOST';
                if ($asForm) {
                    $request = $httpClient->post($path, array('Content-Type' => 'application/x-www-form-urlencoded'), $data);
                } else {
                    $request = $httpClient->post($path, array('Content-Type' => 'application/json'), json_encode($data));
                }
                break;

            case 'PUT':
                $curl[] = '-XPUT';
                if ($asForm) {
                    $request = $httpClient->put($path, array('Content-Type' => 'application/x-www-form-urlencoded'), $data);
                } else {
                    $request = $httpClient->put($path, array('Content-Type' => 'application/json'), json_encode($data));
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
                $request = $httpClient->delete($path);
                break;

            default:
                return 1;
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
            $output->writeln('<info>Request URI:    '.$request->getUrl().'</info>');
            $output->writeln('<info>Request Method: '.$request->getMethod().'</info>');
            $output->writeln('<info>Status Code:    '.$response->getStatusCode().'</info>');
            $output->writeln('<info>Content Type:   '.$response->getContentType().'</info>');

            $res  = $response->getBody(true);
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

    private function _jsonpp($json, $istr = '  ')
    {
        $result = '';
        for ($p = $q = $i = 0; isset($json[$p]); ++$p) {
            $json[$p] == '"' && ($p > 0 ? $json[$p - 1] : '') != '\\' && $q = !$q;
            if (strchr('}]', $json[$p]) && !$q && $i--) {
                strchr('{[', $json[$p - 1]) || $result .= "\n".str_repeat($istr, $i);
            }
            $result .= $json[$p];
            if (strchr(',{[', $json[$p]) && !$q) {
                $i += strchr('{[', $json[$p]) === false ? 0 : 1;
                strchr('}]', $json[$p + 1]) || $result .= "\n".str_repeat($istr, $i);
            }
        }

        return $result;
    }
}
