<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\Portal;

use JMS\Serializer\Annotation as JMS;

/**
 * Class DownloadsSettings.
 */
class DownloadsSettings extends AbstractAppSettings
{
    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    protected $attachment_require_auth;

    /**
     * @return bool
     */
    public function isAttachmentRequireAuth()
    {
        return $this->attachment_require_auth;
    }

    /**
     * @param bool $require
     *
     * @return AbstractAppSettings
     */
    public function setAttachmentRequireAuth($require)
    {
        $this->attachment_require_auth = $require;

        return $this;
    }
}
