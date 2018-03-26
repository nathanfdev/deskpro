<?php

namespace DeskPRO\Bundle\AppBundle\Archive;

class Archive
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var int
     */
    protected $filesCount = 0;

    /**
     * @var bool
     */
    protected $passwordProtected = false;

    /**
     * @var string
     */
    protected $comment = '';

    /**
     * @var string
     */
    protected $status = '';

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return Archive
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return int
     */
    public function getFilesCount()
    {
        return $this->filesCount;
    }

    /**
     * @param int $filesCount
     *
     * @return Archive
     */
    public function setFilesCount($filesCount)
    {
        $this->filesCount = $filesCount;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPasswordProtected()
    {
        return $this->passwordProtected;
    }

    /**
     * @param bool $passwordProtected
     *
     * @return Archive
     */
    public function setPasswordProtected($passwordProtected)
    {
        $this->passwordProtected = $passwordProtected;

        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     *
     * @return Archive
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return Archive
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }
}
