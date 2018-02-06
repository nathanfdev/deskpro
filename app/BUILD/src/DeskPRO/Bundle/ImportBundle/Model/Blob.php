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

namespace DeskPRO\Bundle\ImportBundle\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

/**
 * Exporting base attachment entity.
 *
 * Class AbstractBlob
 *
 * @Assert\GroupSequenceProvider
 */
class Blob implements GroupSequenceProviderInterface
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank(groups={"data"})
     */
    protected $blob_data;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $blob_url;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $blob_path;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank(groups={"common"})
     */
    protected $file_name;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank(groups={"common"})
     */
    protected $content_type;

    /**
     * Blob data.
     *
     * @return string
     */
    public function getBlobData()
    {
        return $this->blob_data;
    }

    /**
     * Set blob data.
     *
     * @param string $blob_data
     *
     * @return $this
     */
    public function setBlobData($blob_data)
    {
        $this->blob_data = $blob_data;

        return $this;
    }

    /**
     * Blob url.
     *
     * @return string
     */
    public function getBlobUrl()
    {
        return $this->blob_url;
    }

    /**
     * Set a blob url.
     *
     * @param string $blob_url
     *
     * @return $this
     */
    public function setBlobUrl($blob_url)
    {
        $this->blob_url = $blob_url;

        return $this;
    }

    /**
     * Blob path.
     *
     * @return string
     */
    public function getBlobPath()
    {
        return $this->blob_path;
    }

    /**
     * Set a blob path.
     *
     * @param string $blob_path
     *
     * @return $this
     */
    public function setBlobPath($blob_path)
    {
        $this->blob_path = $blob_path;

        return $this;
    }

    /**
     * File name.
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->file_name;
    }

    /**
     * Set file name.
     *
     * @param string $file_name
     *
     * @return $this
     */
    public function setFileName($file_name)
    {
        $this->file_name = $file_name;

        return $this;
    }

    /**
     * Content type.
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->content_type;
    }

    /**
     * Set content type.
     *
     * @param string $content_type
     *
     * @return $this
     */
    public function setContentType($content_type)
    {
        $this->content_type = $content_type;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupSequence()
    {
        $groups = ['common'];
        if (!$this->blob_url && !$this->blob_path) {
            $groups[] = 'data';
        }

        return $groups;
    }
}
