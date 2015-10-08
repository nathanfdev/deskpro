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

namespace DeskPRO\Component\TaskRunner;

use DeskPRO\Component\TaskRunner\Processor\ProcessorInterface;
use DeskPRO\Component\TaskRunner\Reader\ReaderInterface;
use Monolog\Logger;
use React\EventLoop\Factory as EventLoopFactory;
use React\EventLoop\LoopInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
    private $task_timeout;

    /**
     * @var int
     */
    private $max_tasks;

    /**
     * @var int
     */
    private $peak_tasks = 0;

    /**
     * @var bool
     */
    private $stop_on_done;

    /**
     * @var TaskHandle[]
     */
    private $running_tasks = array();

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
     * @var float
     */
    private $tick_time = 1.00;

    public function __construct(array $options = array())
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);

        $this->start_time = microtime(true);

        $this->task_timeout = $this->options['task_timeout'];
        $this->max_tasks    = $this->options['max_tasks'];
        $this->stop_on_done = $this->options['stop_on_done'];
        $this->tick_time    = $this->options['tick_time'];
        $this->logger       = $this->options['logger'];
        $this->reader       = $this->options['reader'];
        $this->processor    = $this->options['processor'];
        $this->loop         = $this->options['loop'];
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'task_timeout' => 0,
            'max_tasks'    => 4,
            'stop_on_done' => false,
            'tick_time'    => 1.00,
        ));
        $resolver->setRequired(array(
            'reader',
            'processor',
            'logger',
            'loop',
        ));

        $resolver->setAllowedTypes(array(
            'task_timeout' => 'integer',
            'max_tasks'    => 'integer',
            'tick_time'    => 'float',
            'reader'       => 'DeskPRO\Component\TaskRunner\Reader\ReaderInterface',
            'processor'    => 'DeskPRO\Component\TaskRunner\Processor\ProcessorInterface',
            'logger'       => 'Monolog\Logger',
            'loop'         => 'React\EventLoop\LoopInterface',
        ));

        $resolver->setDefaults(array('loop' => function (Options $options) {
            return EventLoopFactory::create();
        }));
    }

    /**
     * Starts the event loop to start running tasks.
     */
    public function start()
    {
        $this->loop->addPeriodicTimer($this->tick_time, array($this, '_runLoop'));
        $this->loop->run();
    }

    /**
     * @internal
     */
    public function _runLoop()
    {
        $this->housekeeping();

        // Only start this many tasks per loop
        // So housekeeping has a chance to run even if max_tasks if high
        $max_per_tick = 50;

        while (count($this->running_tasks) < $this->max_tasks && ($task = $this->getNext()) && $max_per_tick-- > 0) {
            $handle                              = $this->processor->start($task);
            $this->running_tasks[$task->getId()] = $handle;

            $this->logger->info(
                sprintf('[Task %s] Task started', $task->getId()),
                array('task' => $task, 'log_event' => self::LOG_EV_TASK_START)
            );
        }

        $num = count($this->running_tasks);
        if ($num > $this->peak_tasks) {
            $this->peak_tasks = $num;
        }

        if ($this->stop_on_done && empty($this->running_tasks)) {
            $this->loop->stop();
        }
    }

    /**
     * Clean up ended tasks.
     */
    private function housekeeping()
    {
        foreach ($this->running_tasks as $c) {
            // Check for timeout and terminate any that are too old
            if ($this->task_timeout && (microtime() - $c->getStartTime()) > $this->task_timeout) {
                $this->logger->alert(
                    sprintf('[Task %s] Timed out after %s seconds; terminating', $c->getTask()->getId(), $this->task_timeout),
                    array('task' => $c->getTask(), 'log_event' => self::LOG_EV_TASK_TIMEOUT)
                );

                try {
                    $this->processor->terminate($c);
                } catch (\Exception $e) {
                    $this->logger->error(
                        sprintf('[Task %s] Failed to terminate with exception: %s', $c->getTask()->getId(), $e->getMessage()),
                        array('task' => $c->getTask(), 'exception' => $e, 'log_event' => self::LOG_EV_ERROR)
                    );
                }

                // Check for tasks that are over, we can remove them from our log
            } elseif ($this->processor->status($c) !== ProcessorInterface::STATUS_RUNNING) {
                unset($this->running_tasks[$c->getTask()->getId()]);
            }
        }
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
                array('exception' => $e, 'log_event' => self::LOG_EV_ERROR)
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
