<?php

namespace DeskPRO\Component\TaskRunner;

use DeskPRO\Component\TaskRunner\Processor\ProcessorInterface;
use DeskPRO\Component\TaskRunner\Reader\ReaderInterface;
use Monolog\Logger;
use React\EventLoop\Factory as EventLoopFactory;
use React\EventLoop\LoopInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TaskRunner.
 */
class TaskRunner
{
    const LOG_EV_TASK_START   = 'task_start';
    const LOG_EV_TASK_END     = 'task_end';
    const LOG_EV_TASK_TIMEOUT = 'task_timeout';
    const LOG_EV_TASK_OUTPUT  = 'task_output';
    const LOG_EV_ERROR        = 'error';

    /**
     * @var array
     */
    private $options;

    /**
     * @var float
     */
    private $start_time;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var int
     */
    private $peak_tasks = 0;

    /**
     * @var TaskHandle[]
     */
    private $running_tasks = [];

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var ReaderInterface
     */
    private $reader;

    /**
     * @var ProcessorInterface
     */
    private $processor;

    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);

        $this->start_time = microtime(true);

        $this->logger    = $this->options['logger'];
        $this->reader    = $this->options['reader'];
        $this->processor = $this->options['processor'];
        $this->loop      = $this->options['loop'];
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'task_timeout'    => 0,
                'max_tasks'       => 4,
                'stop_on_done'    => false,
                'stop_after_time' => 0,
                'tick_time'       => 1.00,
            ])
            ->setRequired([
                'reader',
                'processor',
                'logger',
                'loop',
            ])
            ->setAllowedTypes('task_timeout', 'integer')
            ->setAllowedTypes('max_tasks', 'integer')
            ->setAllowedTypes('stop_after_time', 'integer')
            ->setAllowedTypes('tick_time', 'float')
            ->setAllowedTypes('reader', 'DeskPRO\Component\TaskRunner\Reader\ReaderInterface')
            ->setAllowedTypes('processor', 'DeskPRO\Component\TaskRunner\Processor\ProcessorInterface')
            ->setAllowedTypes('logger', 'Monolog\Logger')
            ->setAllowedTypes('loop', 'React\EventLoop\LoopInterface')
        ;

        $resolver->setDefault('loop', EventLoopFactory::create());
    }

    /**
     * Starts the event loop to start running tasks.
     */
    public function start()
    {
        $this->loop->addPeriodicTimer($this->options['tick_time'], [$this, '_runLoop']);

        $this->start_time = microtime(true);

        $this->loop->run();
    }

    /**
     * Are we over our time limit?
     *
     * @return bool
     */
    private function isOverTime()
    {
        if ($this->options['stop_after_time']) {
            $t = microtime(true) - $this->start_time;
            if ($t > $this->options['stop_after_time']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @internal
     */
    public function _runLoop()
    {
        $this->housekeeping();

        if ($this->isOverTime() && empty($this->running_tasks)) {
            $this->loop->stop();
            $this->logger->debug('Stopping loop due to isOverTime');

            return;
        }

        // Only start this many tasks per loop
        // So housekeeping has a chance to run even if max_tasks if high
        $max_per_tick = 50;

        $me = $this;

        if (!$this->isOverTime()) {
            $count = 0;
            while (count($this->running_tasks) < $this->options['max_tasks'] && ($task = $this->getNext()) && $max_per_tick-- > 0) {
                ++$count;

                /** @var TaskHandle $handle */
                $handle                              = $this->processor->start($task, $this->loop);
                $this->running_tasks[$task->getId()] = $handle;

                $this->logger->info(
                    sprintf('[Task %s] Task started', $task->getId()),
                    ['task' => $task, 'log_event' => self::LOG_EV_TASK_START]
                );

                $handle->addEventListener(TaskHandle::EVENT_DONE_SUCCESS, function ($v, TaskHandle $handle) use ($me) {
                    $me->_markTaskHandlerDone($handle, true);
                });
                $handle->addEventListener(TaskHandle::EVENT_DONE_FAILURE, function ($v, TaskHandle $handle) use ($me) {
                    $me->_markTaskHandlerDone($handle, false);
                });
            }

            if ($count) {
                $this->logger->debug(sprintf('[TaskRunner] tick -- got %d new tasks', $count));
            }
        }

        $num = count($this->running_tasks);
        if ($num > $this->peak_tasks) {
            $this->peak_tasks = $num;
        }

        if ($this->options['stop_on_done'] && empty($this->running_tasks)) {
            $this->loop->stop();
            $this->logger->debug('Stopping loop due to stop_on_done option and empty task queue');
        }
    }

    /**
     * Clean up ended tasks.
     */
    private function housekeeping()
    {
        foreach ($this->running_tasks as $c) {
            if ($this->processor->status($c) !== ProcessorInterface::STATUS_RUNNING) {
                $this->_markTaskHandlerDone($c, true);
            // Check for timeout and terminate any that are too old
            } elseif ($this->options['task_timeout'] && (microtime() - $c->getStartTime()) > $this->options['task_timeout']) {
                $this->logger->alert(
                    sprintf('[Task %s] Timed out after %s seconds; terminating', $c->getTask()->getId(), $this->options['task_timeout']),
                    ['task' => $c->getTask(), 'log_event' => self::LOG_EV_TASK_TIMEOUT]
                );

                try {
                    $this->processor->terminate($c);
                } catch (\Exception $e) {
                    $this->logger->error(
                        sprintf('[Task %s] Failed to terminate with exception: %s', $c->getTask()->getId(), $e->getMessage()),
                        ['task' => $c->getTask(), 'exception' => $e, 'log_event' => self::LOG_EV_ERROR]
                    );
                }
            }
        }
    }

    /**
     * @internal
     *
     * @param TaskHandle $c
     * @param bool       $is_success
     */
    public function _markTaskHandlerDone(TaskHandle $c, $is_success)
    {
        if (!$is_success) {
            $this->logger->err(
                sprintf('[Task %s] Task done (failure)', $c->getTask()->getId()),
                ['task' => $c->getTask(), 'log_event' => self::LOG_EV_TASK_END]
            );
        } else {
            $this->logger->info(
                sprintf('[Task %s] Task done (success)', $c->getTask()->getId()),
                ['task' => $c->getTask(), 'log_event' => self::LOG_EV_TASK_END]
            );
        }
        unset($this->running_tasks[$c->getTask()->getId()]);
    }

    /**
     * @return Task\TaskInterface|null
     */
    protected function getNext()
    {
        try {
            return $this->reader->getNext();
        } catch (\Exception $e) {
            $this->logger->error(
                "getNext failed with exception: {$e->getMessage()}",
                ['exception' => $e, 'log_event' => self::LOG_EV_ERROR]
            );

            return;
        }
    }

    /**
     * @return LoopInterface
     */
    public function getLoop()
    {
        return $this->loop;
    }

    /**
     * @return ReaderInterface
     */
    public function getReader()
    {
        return $this->reader;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return int
     */
    public function getPeakTasks()
    {
        return $this->peak_tasks;
    }
}
