<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
