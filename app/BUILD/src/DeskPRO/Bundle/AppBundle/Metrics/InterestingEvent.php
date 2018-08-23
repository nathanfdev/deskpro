<?php

namespace DeskPRO\Bundle\AppBundle\Metrics;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class InterestingEvent extends Event
{
    const NAME = 'metrics.interesting_event';

    /**
     * An id for the thing that happened.
     * This represents the event itself. i.e. importer.started.
     *
     * @var string
     */
    private $id = '';

    /**
     * Any related info about the event.
     *
     * @var array
     */
    private $info = [];

    /**
     * @param EventDispatcherInterface $dispatcher
     * @param string                   $id
     * @param array                    $info
     *
     * @return InterestingEvent
     */
    public static function createAndDispatch(EventDispatcherInterface $dispatcher, $id, $info = [])
    {
        $e = new self($id, $info);

        return $dispatcher->dispatch(self::NAME, $e);
    }

    /**
     * @param string $id
     * @param array  $info
     */
    public function __construct($id, array $info = [])
    {
        $this->id   = $id;
        $this->info = $info;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return array
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @param string $path    The property path
     * @param mixed  $default The value to return if unset
     *
     * @return mixed
     */
    public function getInfoPath($path, $default = null)
    {
        try {
            return PropertyAccess::createPropertyAccessor()->getValue($this->info, $path);
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * @param string $path
     * @param mixed  $value
     *
     * @return $this
     */
    public function setInfoPath($path, $value)
    {
        PropertyAccess::createPropertyAccessor()->setValue($this->info, $path, $value);

        return $this;
    }

    /**
     * @param array $info
     *
     * @return $this
     */
    public function setInfo($info)
    {
        $this->info = $info;

        return $this;
    }
}
