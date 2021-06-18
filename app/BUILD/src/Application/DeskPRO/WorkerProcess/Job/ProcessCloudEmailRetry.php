<?php

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use DeskPRO\Bundle\AppBundle\Util\HttpClient;
use DpSys\LowError\SystemErrorHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class ProcessCloudEmailRetry
 *
 * @package Application\DeskPRO\WorkerProcess\Job
 */
class ProcessCloudEmailRetry extends AbstractJob
{
    const DEFAULT_INTERVAL      = 1;
    const BATCH_SIZE            = 100;
    const MAX_EXECUTION_SECONDS = 45;

    /**
     * {@inheritDoc}
     */
    public function run()
    {
        if (!defined('DPC_IS_CLOUD')) {
            return;
        }

        if (!App::getSetting('cron.disable_email_gateway_job')) {
            return;
        }

        $start  = time();
        $client = new HttpClient();
        $router = $this->getContainer()->get('router');

        $sources = App::getDb()->executeQuery("
            SELECT s.uuid
            FROM email_sources s
            WHERE s.status = 'retry'
            LIMIT ?
        ", [self::BATCH_SIZE], [\PDO::PARAM_INT]);

        $uuids = array_map(function ($source) {
            return $source['uuid'];
        }, iterator_to_array($sources));

        foreach ($uuids as $uuid) {
            $execTime = time() - $start;

            if ($execTime >= self::MAX_EXECUTION_SECONDS) {
                $this->getLogger()->log(
                    sprintf('Maximum execution time of %d seconds reached while processing retried emails, awaiting'
                        .' next run to continue processing', self::MAX_EXECUTION_SECONDS),
                    'INFO'
                );
                break;
            }

            $this->getLogger()->log("Executing retry for message {$uuid}", 'DEBUG');

            $url = $router->generate(
                'api_v2_incoming_email_execute',
                ['uuid' => $uuid],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            try {
                $response = $client->post($url, $this->buildRequestOptions());
                if (!in_array($response->getStatusCode(), [200])) {
                    $this->handleError($uuid);
                    $this->getLogger()->log(
                        "Failed to execute on retry of message {$uuid}, with response status {$response->getStatusCode()}",
                        'ERR'
                    );
                }
            } catch (\Exception $e) {
                $this->handleError($uuid);
                SystemErrorHandler::logException($e);
                $this->getLogger()->log(
                    "Failed to execute on retry of message {$uuid}",
                    'ERR'
                );
            }
        }
    }

    private function handleError($sourceUuid)
    {
        App::getDb()->executeUpdate("
            UPDATE email_sources
            SET status = 'server_error'
            WHERE uuid = ?
        ", [$sourceUuid]);
    }

    /**
     * @return array[]
     * @throws \Exception
     */
    private function buildRequestOptions()
    {
        $masterKey = $this->getContainer()->get('settings_resolver')
            ->getGlobalSettings()
            ->get('api_auth.master_key')
        ;

        return [
            'headers' => [
                'Authorization' => sprintf('key %s', $masterKey),
            ],
        ];
    }
}
