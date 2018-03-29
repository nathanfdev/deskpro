<?php

namespace DeskPRO\Bundle\ImportBundle\Model;

/**
 * Entity containing attachments interface.
 *
 * Interface AttachmentsAwareInterface
 */
interface AttachmentsAwareInterface
{
    /**
     * Returns a collection of attachments.
     *
     * @return Attachment[]
     */
    public function getAttachments();

    /**
     * Add an attachment.
     *
     * @param Attachment $attachment
     *
     * @return $this
     */
    public function addAttachment(Attachment $attachment);
}
