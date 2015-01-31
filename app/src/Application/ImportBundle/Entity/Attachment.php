<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\Entity;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints;

/**
 * Exporting attachment entity
 *
 * Class TicketMessageAttachment
 * @package Application\ImportBundle\Entity
 */
final class Attachment extends AbstractEntity
{
    /**
     * @var string
     */
    private $person_email;

    /**
     * @var string
     */
    private $blob_data;

    /**
     * @var string
     */
    private $blob_url;

    /**
     * @var string
     */
    private $blob_path;

    /**
     * @var string
     */
    private $file_name;

    /**
     * @var string
     */
    private $content_type;

    /**
     * @var bool
     */
    private $is_inline = false;

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_ATTACHMENT;
    }

    /**
     * @return string
     */
    public function getPersonEmail()
    {
        return $this->person_email;
    }

    /**
     * @param string $person_email
     * @return $this
     */
    public function setPersonEmail($person_email)
    {
        $this->person_email = $person_email;
        return $this;
    }

    /**
     * @return string
     */
    public function getBlobData()
    {
        return $this->blob_data;
    }

    /**
     * @param string $blob_data
     * @return $this
     */
    public function setBlobData($blob_data)
    {
        $this->blob_data = $blob_data;
        return $this;
    }

    /**
     * @return string
     */
    public function getBlobUrl()
    {
        return $this->blob_url;
    }

    /**
     * @param string $blob_url
     * @return $this
     */
    public function setBlobUrl($blob_url)
    {
        $this->blob_url = $blob_url;
        return $this;
    }

    /**
     * @return string
     */
    public function getBlobPath()
    {
        return $this->blob_path;
    }

    /**
     * @param string $blob_path
     * @return $this
     */
    public function setBlobPath($blob_path)
    {
        $this->blob_path = $blob_path;
        return $this;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->file_name;
    }

    /**
     * @param string $file_name
     * @return $this
     */
    public function setFileName($file_name)
    {
        $this->file_name = $file_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->content_type;
    }

    /**
     * @param string $content_type
     * @return $this
     */
    public function setContentType($content_type)
    {
        $this->content_type = $content_type;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isInline()
    {
        return $this->is_inline;
    }

    /**
     * @param boolean $is_inline
     * @return $this
     */
    public function setAsInline($is_inline)
    {
        $this->is_inline = $is_inline;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return array(
            'oid'          => $this->oid,
            'person'       => $this->person_email,
            'blob_data'    => $this->blob_data,
            'blob_url'     => $this->blob_url,
            'blob_path'    => $this->blob_path,
            'file_name'    => $this->file_name,
            'content_type' => $this->content_type,
            'is_inline'    => $this->is_inline,
        );
    }

    /**
     * Validator class metadata
     *
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata
            ->addPropertyConstraint('oid', new Constraints\NotBlank())
            ->addPropertyConstraint('person_email', new Constraints\Email())
            ->addPropertyConstraint('file_name', new Constraints\NotBlank())
            ->addPropertyConstraint('content_type', new Constraints\NotBlank());
    }
}
