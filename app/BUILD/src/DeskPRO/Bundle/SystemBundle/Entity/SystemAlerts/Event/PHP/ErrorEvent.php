<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\PHP;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\AbstractEvent;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class ErrorEvent.
 *
 * @ORM\Entity
 */
class ErrorEvent extends AbstractEvent
{
    /**
     * @var int
     *
     * @ORM\Column(name="error_type", type="integer", options={"unsigned"=true})
     */
    protected $errorType;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $message;

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
     * @var string
     *
     * @ORM\Column(type="json_array")
     */
    protected $data;

    /**
     * @param string    $errorType
     * @param string    $message
     * @param string    $file
     * @param int       $line
     * @param \DateTime $dateCreated
     * @param array     $data
     */
    public function __construct($errorType, $message, $file, $line, \DateTime $dateCreated = null, $data = [])
    {
        $this->errorType = $errorType;
        $this->message   = $message;
        $this->file      = $file;
        $this->line      = $line;
        $this->data      = $data;
        parent::__construct($dateCreated);
    }

    /**
     * {@inheritdoc}
     */
    protected function generateSubjectUniqueId()
    {
        return $this->errorType.'-'.md5($this->file).'-'.$this->line;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubjectDescription()
    {
        return "PHP error \"{$this->message}\"";
    }

    /**
     * If error is critical.
     *
     * @return bool
     */
    public function isCritical()
    {
        return in_array(intval($this->errorType), [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->errorType;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
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
