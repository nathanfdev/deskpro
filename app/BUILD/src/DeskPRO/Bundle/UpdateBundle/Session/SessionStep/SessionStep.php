<?php

namespace DeskPRO\Bundle\UpdateBundle\Session\SessionStep;

class SessionStep implements \JsonSerializable
{
    const WAITING  = 'waiting';
    const RUNNING  = 'running';
    const FINISHED = 'finished';
    const OK       = 'ok';
    const WARN     = 'warning';
    const ERROR    = 'error';

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $status = self::WAITING;

    /**
     * @var string
     */
    private $finishStatus = self::OK;

    /**
     * @var string
     */
    private $details = '';

    /**
     * @var string
     */
    private $summaryText = '';

    /**
     * @var array
     */
    protected $data = [];

    /**
     * SessionStep constructor.
     *
     * @param string $title
     */
    public function __construct($title)
    {
        $this->title = $title;
        $this->init();
    }

    protected function init()
    {
    }

    /**
     * @return $this
     */
    public function start()
    {
        $this->status = self::RUNNING;

        return $this;
    }

    /**
     * @param string $summaryText
     * @param string $details
     *
     * @return $this
     */
    public function finished($summaryText, $details = '')
    {
        $this->status      = self::FINISHED;
        $this->summaryText = $summaryText;
        $this->details     = $details;

        return $this;
    }

    /**
     * @param string $summaryText
     * @param string $details
     *
     * @return $this
     */
    public function finishedWithWarning($summaryText, $details = '')
    {
        $this->finishStatus = self::WARN;
        $this->finished($summaryText, $details);

        return $this;
    }

    /**
     * @param string $summaryText
     * @param string $details
     *
     * @return $this
     */
    public function finishedWithError($summaryText, $details = '')
    {
        $this->finishStatus = self::ERROR;
        $this->finished($summaryText, $details);

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getSummary()
    {
        return $this->summaryText;
    }

    /**
     * @return string
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @return bool
     */
    public function isRunning()
    {
        return $this->status === self::RUNNING;
    }

    /**
     * @return bool
     */
    public function isFinished()
    {
        return $this->status === self::FINISHED;
    }

    /**
     * @return bool
     */
    public function isWaiting()
    {
        return $this->status === self::WAITING;
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return $this->isFinished() && $this->finishStatus === self::ERROR;
    }

    /**
     * @return bool
     */
    public function isWarning()
    {
        return $this->isFinished() && $this->finishStatus === self::WARN;
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->isFinished() && $this->finishStatus === self::OK;
    }

    /**
     /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $dat                   = [];
        $dat['status']         = $this->status;
        $dat['finishedStatus'] = $this->isFinished() ? $this->finishStatus : null;
        $dat['summary']        = $this->summaryText;
        $dat['details']        = $this->details;
        $dat['data']           = $this->data;

        return $dat;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set state on this object based on new step object.
     *
     * @param SessionStep $step
     */
    public function merge(SessionStep $step)
    {
        if ($step->isFinished()) {
            if ($step->isError()) {
                $this->finishedWithError($step->getSummary(), $step->getDetails());
            } elseif ($step->isWarning()) {
                $this->finishedWithWarning($step->getSummary(), $step->getDetails());
            } else {
                $this->finished($step->getSummary(), $step->getDetails());
            }
        } elseif ($step->isRunning()) {
            $this->start();
        }

        $this->data = array_merge($this->data, $step->getData());
    }
}
