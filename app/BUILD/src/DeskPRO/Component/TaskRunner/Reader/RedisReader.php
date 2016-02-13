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

namespace DeskPRO\Component\TaskRunner\Reader;

use Monolog\Logger;
use Predis\Client;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
        $resolver->setDefaults(array(
            'redis_client'  => null,
            'redis_params'  => null, // see https://github.com/nrk/predis/wiki/Connection-Parameters
            'redis_factory' => null,
        ));

        $resolver->setRequired(array(
            'logger',
            'redis_key',
            'task_factory',
        ));
        $resolver->setOptional(array(
            'logger',
            'redis_client',
            'redis_params',
            'redis_factory',
        ));

        $resolver->setAllowedTypes(array(
            'redis_client' => array('Predis\Client', 'null'),
            'logger'       => 'Monolog\Logger',
            'task_factory' => 'DeskPRO\Component\TaskRunner\Task\TaskFactoryInterface',
            'redis_params' => array('array', 'null'),
            'redis_key'    => array('string', 'null'),
        ));
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
                $this->logger->warning(sprintf('[Redis] getNext failure (attempt %s): %s', $i, $e->getMessage()), array('exception' => $e));
                $last_e = $e;
            }

            if ($next) {
                try {
                    $task = $this->task_factory->createTask($next);

                    return $task;
                } catch (\Exception $e) {
                    $this->logger->warning(sprintf('[Redis] Invalid task data: %s', $next), array('task_data' => $next));
                }
            }
        }

        if ($last_e) {
            throw $last_e;
        }

        return;
    }
}
