<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Exception;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\AbstractEvent;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class AbstractExceptionEvent.
 *
 * @ORM\MappedSuperclass
 */
abstract class AbstractExceptionEvent extends AbstractEvent
{
    /**
     * @var string
     *
     * @ORM\Column(name="exception_class", type="string")
     */
    protected $class;

    /**
     * @var int
     *
     * @ORM\Column(name="exception_code", type="integer", options={"unsigned"=true})
     */
    protected $code = 0;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    protected $message;

    /**
     * @var array
     *
     * @ORM\Column(name="trace", type="json_array")
     */
    protected $trace;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $file;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    protected $line;

    /**
     * ExceptionEvent constructor.
     *
     * @param \Exception     $exception
     * @param \DateTime|null $dateCreated
     */
    public function __construct(\Exception $exception, \DateTime $dateCreated = null)
    {
        $this->class   = get_class($exception);
        $this->code    = $exception->getCode();
        $this->message = $exception->getMessage();
        $this->trace   = $exception->getTrace();
        $this->file    = $exception->getFile();
        $this->line    = $exception->getLine();
        parent::__construct($dateCreated);
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return array
     */
    public function getTrace()
    {
        return $this->trace;
    }

    /**
     * @return array
     */
    public function getTraceAsString()
    {
        return print_r($this->trace, true);
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return int
     */
    public function getLine()
    {
        return $this->line;
    }
}
