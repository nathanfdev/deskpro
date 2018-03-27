<?php

namespace DeskPRO\Component\TaskRunner\Reader;

use Monolog\Logger;
use Predis\Client;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RedisReader.
 */
class RedisReader implements ReaderInterface
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var array
     */
    private $options;

    /**
     * @var \Predis\Client
     */
    private $redis_client = null;

    /**
     * @var string
     */
    private $redis_key;

    /**
     * @var \DeskPRO\Component\TaskRunner\Task\TaskFactoryInterface
     */
    private $task_factory;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);

        if (empty($this->options['redis_client']) && empty($this->options['redis_params']) && empty($this->options['redis_factory'])) {
            throw new MissingOptionsException('Need one of: redis_client, redis_params, redis_factory');
        }

        if ($this->options['redis_client']) {
            $this->redis_client = $this->options['redis_client'];
        }

        $this->redis_key    = $this->options['redis_key'];
        $this->task_factory = $this->options['task_factory'];
        $this->logger       = $this->options['logger'];
    }

    /**
     * @param OptionsResolver $resolver
     */
    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'redis_client'  => null,
                'redis_params'  => null, // see https://github.com/nrk/predis/wiki/Connection-Parameters
                'redis_factory' => null,
            ])
            ->setRequired([
                'logger',
                'redis_key',
                'task_factory',
            ])
            ->setDefined([
                'logger',
                'redis_client',
                'redis_params',
                'redis_factory',
            ])
            ->setAllowedTypes('redis_client', ['Predis\Client', 'null'])
            ->setAllowedTypes('logger', 'Monolog\Logger')
            ->setAllowedTypes('task_factory', 'DeskPRO\Component\TaskRunner\Task\TaskFactoryInterface')
            ->setAllowedTypes('redis_params', ['array', 'null'])
            ->setAllowedTypes('redis_key', ['string', 'null'])
        ;
    }

    /**
     * @throws \Exception
     *
     * @return Client
     */
    private function getRedisClient()
    {
        if ($this->redis_client && $this->redis_client->isConnected()) {
            return $this->redis_client;
        }

        if ($this->options['redis_factory']) {
            $this->logger->info('[Redis] Connecting (factory)...');
            try {
                /** @var Client $client */
                $client = call_user_func($this->options['redis_factory']);
                if (!$client->isConnected()) {
                    $client->connect();
                }
            } catch (\Exception $e) {
                $this->logger->error('[Redis] -> Connect failure: '.$e->getMessage());
                throw $e;
            }
        } elseif ($this->options['redis_params']) {
            $this->logger->info('[Redis] Connecting (params)...');
            try {
                $client = new Client($this->options['redis_params']);
                $client->connect();
            } catch (\Exception $e) {
                $this->logger->error('[Redis] -> Connect failure: '.$e->getMessage());
                throw $e;
            }
        } else {
            $this->logger->error('[Redis] -> Client is disconnected and no way to establish new connection');
            throw new \RuntimeException('Cannot reconnect');
        }

        $this->logger->info('[Redis] Done connection');

        $this->redis_client = $client;

        return $this->redis_client;
    }

    /**
     * @throws \Exception
     *
     * @return null|\DeskPRO\Component\TaskRunner\Task\TaskInterface
     */
    public function getNext()
    {
        $last_e = null;
        for ($i = 0; $i < 5; ++$i) {
            $last_e = null;
            $next   = null;

            try {
                $client = $this->getRedisClient();
                $next   = $client->rpop($this->redis_key);

                if ($next === null) {
                    return;
                }
            } catch (\Exception $e) {
                $this->logger->warning(sprintf('[Redis] getNext failure (attempt %s): %s', $i, $e->getMessage()), ['exception' => $e]);
                $last_e = $e;
            }

            if ($next) {
                try {
                    $task = $this->task_factory->createTask($next);

                    return $task;
                } catch (\Exception $e) {
                    $this->logger->warning(sprintf('[Redis] Invalid task data: %s', $next), ['task_data' => $next]);
                }
            }
        }

        if ($last_e) {
            throw $last_e;
        }

        return;
    }
}
